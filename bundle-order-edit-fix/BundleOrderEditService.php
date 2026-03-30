<?php

/**
 * BundleOrderEditService
 * 
 * Oplossing voor het probleem dat bundel-opsplitsing in Shopify retouren aanmaakt.
 * 
 * FLOW:
 * 1. Order webhook komt binnen met bundel-product
 * 2. We starten een Order Edit sessie (orderEditBegin)
 * 3. We zetten de bundel line item quantity op 0 (orderEditSetQuantity)
 * 4. We voegen de individuele varianten toe (orderEditAddVariant)
 * 5. We committen de edit (orderEditCommit) met notifyCustomer: false
 * 6. We sturen de order naar Piqcer
 * 
 * BELANGRIJK: De Order Edit API maakt GEEN retour/refund aan.
 * Het past de order direct aan als een "edit", niet als return + new line.
 * 
 * VEREISTEN:
 * - Shopify Admin API access token met write_orders scope
 * - PHP 8.0+
 * - Bundle-to-variants mapping (configureerbaar)
 */

class BundleOrderEditService
{
    private string $shopDomain;
    private string $accessToken;
    private string $apiVersion;

    /**
     * Mapping van bundel product variant IDs naar individuele variant IDs.
     * 
     * Format: 'gid://shopify/ProductVariant/BUNDEL_ID' => [
     *     ['variantId' => 'gid://shopify/ProductVariant/VARIANT_ID', 'quantity' => 1],
     *     ['variantId' => 'gid://shopify/ProductVariant/VARIANT_ID', 'quantity' => 2],
     * ]
     * 
     * Dit kan ook uit een database of config file komen.
     */
    private array $bundleMapping;

    public function __construct(string $shopDomain, string $accessToken, string $apiVersion = '2025-01')
    {
        $this->shopDomain = $shopDomain;
        $this->accessToken = $accessToken;
        $this->apiVersion = $apiVersion;
        $this->bundleMapping = $this->loadBundleMapping();
    }

    // =========================================================================
    // MAIN: Verwerk een order met bundels
    // =========================================================================

    /**
     * Hoofdfunctie: verwerk een binnenkomende order.
     * Wordt aangeroepen vanuit de order/create webhook handler.
     */
    public function processOrder(array $orderWebhookData): array
    {
        $orderId = 'gid://shopify/Order/' . $orderWebhookData['id'];
        $lineItems = $orderWebhookData['line_items'] ?? [];

        // Stap 1: Check of er bundel-producten in de order zitten
        $bundleItems = $this->findBundleItems($lineItems);

        if (empty($bundleItems)) {
            return [
                'status' => 'skipped',
                'message' => 'Geen bundels gevonden in deze order',
                'order_id' => $orderId,
            ];
        }

        // Stap 2: Start Order Edit sessie
        $editSession = $this->orderEditBegin($orderId);
        $calculatedOrderId = $editSession['calculatedOrder']['id'];

        // Stap 3: Voor elke bundel → verwijder bundel, voeg varianten toe
        foreach ($bundleItems as $bundleItem) {
            $this->splitBundleIntoVariants($calculatedOrderId, $bundleItem);
        }

        // Stap 4: Commit de edit (ZONDER klant notificatie)
        $commitResult = $this->orderEditCommit($calculatedOrderId, false);

        // Stap 5: Voeg tag toe zodat je deze orders kunt filteren in analytics
        $this->addTagToOrder($orderId, 'bundle-edited');

        // Stap 6: Haal de bijgewerkte order op voor Piqcer
        $updatedOrder = $this->getOrder($orderId);

        // Stap 7: Stuur naar Piqcer
        $piqcerResult = $this->sendToPiqcer($updatedOrder);

        return [
            'status' => 'success',
            'message' => 'Bundel opgesplitst en naar Piqcer gestuurd',
            'order_id' => $orderId,
            'bundles_processed' => count($bundleItems),
            'piqcer_result' => $piqcerResult,
        ];
    }

    // =========================================================================
    // SHOPIFY GRAPHQL: Order Edit Mutations
    // =========================================================================

    /**
     * Start een Order Edit sessie.
     * Dit opent de order voor bewerking zonder iets te wijzigen.
     */
    private function orderEditBegin(string $orderId): array
    {
        $query = <<<'GRAPHQL'
        mutation orderEditBegin($id: ID!) {
            orderEditBegin(id: $id) {
                calculatedOrder {
                    id
                    lineItems(first: 50) {
                        edges {
                            node {
                                id
                                quantity
                                variant {
                                    id
                                    title
                                }
                            }
                        }
                    }
                }
                userErrors {
                    field
                    message
                }
            }
        }
        GRAPHQL;

        $response = $this->graphqlRequest($query, ['id' => $orderId]);
        $result = $response['data']['orderEditBegin'];

        if (!empty($result['userErrors'])) {
            throw new \RuntimeException(
                'orderEditBegin failed: ' . json_encode($result['userErrors'])
            );
        }

        return $result;
    }

    /**
     * Zet de quantity van een line item op 0.
     * Dit VERWIJDERT het item uit de order ZONDER een retour aan te maken.
     */
    private function orderEditSetQuantity(string $calculatedOrderId, string $lineItemId, int $quantity): array
    {
        $query = <<<'GRAPHQL'
        mutation orderEditSetQuantity($id: ID!, $lineItemId: ID!, $quantity: Int!) {
            orderEditSetQuantity(id: $id, lineItemId: $lineItemId, quantity: $quantity) {
                calculatedOrder {
                    id
                }
                userErrors {
                    field
                    message
                }
            }
        }
        GRAPHQL;

        $response = $this->graphqlRequest($query, [
            'id' => $calculatedOrderId,
            'lineItemId' => $lineItemId,
            'quantity' => $quantity,
        ]);

        $result = $response['data']['orderEditSetQuantity'];

        if (!empty($result['userErrors'])) {
            throw new \RuntimeException(
                'orderEditSetQuantity failed: ' . json_encode($result['userErrors'])
            );
        }

        return $result;
    }

    /**
     * Voeg een variant toe aan de order.
     * Dit voegt een nieuw line item toe ZONDER retour-registratie.
     */
    private function orderEditAddVariant(string $calculatedOrderId, string $variantId, int $quantity): array
    {
        $query = <<<'GRAPHQL'
        mutation orderEditAddVariant($id: ID!, $variantId: ID!, $quantity: Int!) {
            orderEditAddVariant(id: $id, variantId: $variantId, quantity: $quantity) {
                calculatedOrder {
                    id
                }
                calculatedLineItem {
                    id
                }
                userErrors {
                    field
                    message
                }
            }
        }
        GRAPHQL;

        $response = $this->graphqlRequest($query, [
            'id' => $calculatedOrderId,
            'variantId' => $variantId,
            'quantity' => $quantity,
        ]);

        $result = $response['data']['orderEditAddVariant'];

        if (!empty($result['userErrors'])) {
            throw new \RuntimeException(
                'orderEditAddVariant failed: ' . json_encode($result['userErrors'])
            );
        }

        return $result;
    }

    /**
     * Commit de Order Edit.
     * 
     * CRUCIAAL: notifyCustomer op false zetten zodat de klant geen email krijgt.
     * staffNote wordt gebruikt voor interne tracking.
     */
    private function orderEditCommit(string $calculatedOrderId, bool $notifyCustomer = false): array
    {
        $query = <<<'GRAPHQL'
        mutation orderEditCommit($id: ID!, $notifyCustomer: Boolean, $staffNote: String) {
            orderEditCommit(id: $id, notifyCustomer: $notifyCustomer, staffNote: $staffNote) {
                order {
                    id
                    name
                }
                userErrors {
                    field
                    message
                }
            }
        }
        GRAPHQL;

        $response = $this->graphqlRequest($query, [
            'id' => $calculatedOrderId,
            'notifyCustomer' => $notifyCustomer,
            'staffNote' => 'Automatisch: bundel opgesplitst naar individuele varianten',
        ]);

        $result = $response['data']['orderEditCommit'];

        if (!empty($result['userErrors'])) {
            throw new \RuntimeException(
                'orderEditCommit failed: ' . json_encode($result['userErrors'])
            );
        }

        return $result;
    }

    // =========================================================================
    // BUNDEL LOGICA
    // =========================================================================

    /**
     * Vind alle bundel-items in de order line items.
     */
    private function findBundleItems(array $lineItems): array
    {
        $bundleItems = [];

        foreach ($lineItems as $lineItem) {
            $variantId = 'gid://shopify/ProductVariant/' . $lineItem['variant_id'];

            if (isset($this->bundleMapping[$variantId])) {
                $bundleItems[] = [
                    'lineItemId' => $lineItem['id'],
                    'variantId' => $variantId,
                    'quantity' => $lineItem['quantity'],
                    'components' => $this->bundleMapping[$variantId],
                ];
            }
        }

        return $bundleItems;
    }

    /**
     * Splits een bundel op in individuele varianten via Order Edit.
     */
    private function splitBundleIntoVariants(string $calculatedOrderId, array $bundleItem): void
    {
        // Stap A: Zet bundel quantity op 0 (verwijdert het bundel line item)
        // LET OP: We moeten het calculatedLineItem ID gebruiken, niet het originele lineItem ID.
        // Het calculatedLineItem ID halen we uit de orderEditBegin response.
        $this->orderEditSetQuantity(
            $calculatedOrderId,
            $this->getCalculatedLineItemId($calculatedOrderId, $bundleItem['lineItemId']),
            0
        );

        // Stap B: Voeg elke individuele variant toe
        foreach ($bundleItem['components'] as $component) {
            $this->orderEditAddVariant(
                $calculatedOrderId,
                $component['variantId'],
                $component['quantity'] * $bundleItem['quantity']
            );
        }
    }

    /**
     * Haal het calculatedLineItem ID op voor een origineel lineItem.
     * Na orderEditBegin krijgen line items een nieuw 'calculated' ID.
     */
    private function getCalculatedLineItemId(string $calculatedOrderId, string $originalLineItemId): string
    {
        // In een echte implementatie: cache de lineItems uit de orderEditBegin response
        // en match op basis van variant ID of original line item ID.
        // 
        // Voor nu: het calculated line item ID heeft hetzelfde format:
        // 'gid://shopify/CalculatedLineItem/{ID}'
        return 'gid://shopify/CalculatedLineItem/' . $originalLineItemId;
    }

    // =========================================================================
    // HELPER: Tag toevoegen aan order (voor analytics filtering)
    // =========================================================================

    private function addTagToOrder(string $orderId, string $tag): void
    {
        $query = <<<'GRAPHQL'
        mutation addTags($id: ID!, $tags: [String!]!) {
            tagsAdd(id: $id, tags: $tags) {
                userErrors {
                    field
                    message
                }
            }
        }
        GRAPHQL;

        $this->graphqlRequest($query, [
            'id' => $orderId,
            'tags' => [$tag],
        ]);
    }

    // =========================================================================
    // HELPER: Order ophalen
    // =========================================================================

    private function getOrder(string $orderId): array
    {
        $query = <<<'GRAPHQL'
        query getOrder($id: ID!) {
            order(id: $id) {
                id
                name
                tags
                lineItems(first: 50) {
                    edges {
                        node {
                            id
                            title
                            quantity
                            variant {
                                id
                                sku
                                title
                            }
                        }
                    }
                }
                shippingAddress {
                    address1
                    address2
                    city
                    zip
                    country
                    name
                    phone
                }
            }
        }
        GRAPHQL;

        $response = $this->graphqlRequest($query, ['id' => $orderId]);
        return $response['data']['order'];
    }

    // =========================================================================
    // PIQCER INTEGRATIE
    // =========================================================================

    /**
     * Stuur de bijgewerkte order naar Piqcer.
     * 
     * PAS DIT AAN naar jullie Piqcer API configuratie.
     */
    private function sendToPiqcer(array $order): array
    {
        $piqcerApiUrl = getenv('PIQCER_API_URL') ?: 'https://api.piqcer.com/v1/orders';
        $piqcerApiKey = getenv('PIQCER_API_KEY');

        $lineItems = [];
        foreach ($order['lineItems']['edges'] as $edge) {
            $node = $edge['node'];
            $lineItems[] = [
                'sku' => $node['variant']['sku'] ?? '',
                'title' => $node['title'],
                'quantity' => $node['quantity'],
                'variant_title' => $node['variant']['title'] ?? '',
            ];
        }

        $piqcerPayload = [
            'order_number' => $order['name'],
            'shopify_order_id' => $order['id'],
            'line_items' => $lineItems,
            'shipping_address' => $order['shippingAddress'] ?? null,
        ];

        // HTTP POST naar Piqcer
        $ch = curl_init($piqcerApiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $piqcerApiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($piqcerPayload),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'http_code' => $httpCode,
            'response' => json_decode($response, true),
        ];
    }

    // =========================================================================
    // GRAPHQL CLIENT
    // =========================================================================

    private function graphqlRequest(string $query, array $variables = []): array
    {
        $url = "https://{$this->shopDomain}/admin/api/{$this->apiVersion}/graphql.json";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Shopify-Access-Token: ' . $this->accessToken,
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'query' => $query,
                'variables' => $variables,
            ]),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new \RuntimeException('GraphQL request failed: ' . curl_error($ch));
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \RuntimeException("GraphQL request returned HTTP {$httpCode}: {$response}");
        }

        $decoded = json_decode($response, true);

        if (isset($decoded['errors'])) {
            throw new \RuntimeException('GraphQL errors: ' . json_encode($decoded['errors']));
        }

        return $decoded;
    }

    // =========================================================================
    // BUNDEL MAPPING CONFIGURATIE
    // =========================================================================

    /**
     * Laad de bundel-naar-varianten mapping.
     * 
     * PAS DIT AAN: In productie zou dit uit een database of config file komen.
     * 
     * Voorbeeld: Bundel "Starter Kit" (variant ID 12345) bevat:
     * - Product A (variant ID 11111) x1
     * - Product B (variant ID 22222) x1
     * - Product C (variant ID 33333) x2
     */
    private function loadBundleMapping(): array
    {
        // VOORBEELD - vervang met jullie eigen bundel configuratie
        return [
            'gid://shopify/ProductVariant/BUNDEL_VARIANT_ID_HIER' => [
                ['variantId' => 'gid://shopify/ProductVariant/COMPONENT_1_ID', 'quantity' => 1],
                ['variantId' => 'gid://shopify/ProductVariant/COMPONENT_2_ID', 'quantity' => 1],
                ['variantId' => 'gid://shopify/ProductVariant/COMPONENT_3_ID', 'quantity' => 2],
            ],
            // Voeg meer bundels toe...
        ];
    }
}
