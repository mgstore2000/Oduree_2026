<?php

/**
 * VOORBEELD: Webhook handler voor order/create
 * 
 * Dit script ontvangt de Shopify order/create webhook,
 * en roept de BundleOrderEditService aan om bundels op te splitsen.
 * 
 * INTEGRATIE:
 * Lesley kan dit integreren in de bestaande webhook handler
 * of als vervanging gebruiken voor de huidige bundel-logica.
 */

require_once __DIR__ . '/BundleOrderEditService.php';

// ============================================================================
// CONFIGURATIE - Pas aan naar jullie omgeving
// ============================================================================

$config = [
    'shop_domain'  => getenv('SHOPIFY_SHOP_DOMAIN') ?: 'jouw-store.myshopify.com',
    'access_token' => getenv('SHOPIFY_ACCESS_TOKEN'),
    'api_version'  => '2025-01',
    'webhook_secret' => getenv('SHOPIFY_WEBHOOK_SECRET'),
];

// ============================================================================
// WEBHOOK VERIFICATIE
// ============================================================================

$rawBody = file_get_contents('php://input');
$hmacHeader = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';

$calculatedHmac = base64_encode(hash_hmac('sha256', $rawBody, $config['webhook_secret'], true));

if (!hash_equals($calculatedHmac, $hmacHeader)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid webhook signature']);
    exit;
}

// ============================================================================
// ORDER VERWERKEN
// ============================================================================

$orderData = json_decode($rawBody, true);

try {
    $service = new BundleOrderEditService(
        $config['shop_domain'],
        $config['access_token'],
        $config['api_version']
    );

    $result = $service->processOrder($orderData);

    // Log het resultaat
    error_log(sprintf(
        '[BundleEdit] Order %s: %s - %s',
        $orderData['name'] ?? $orderData['id'],
        $result['status'],
        $result['message']
    ));

    http_response_code(200);
    echo json_encode($result);

} catch (\Exception $e) {
    error_log(sprintf(
        '[BundleEdit] ERROR Order %s: %s',
        $orderData['name'] ?? $orderData['id'],
        $e->getMessage()
    ));

    // Shopify verwacht een 200 response, anders probeert het opnieuw.
    // Bij een fout: log het en return 200 om retry-loops te voorkomen.
    // In productie: stuur een alert naar Slack/email.
    http_response_code(200);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ]);
}
