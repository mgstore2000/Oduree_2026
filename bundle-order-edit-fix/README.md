# Bundel Order Edit Fix

## Probleem
Wanneer een bestelling met een bundel binnenkomt, wordt de bundel opgesplitst in individuele varianten. Dit veroorzaakt dat Shopify de verwijderde bundel-items als **retouren** registreert in de analytics.

## Oplossing
Deze code gebruikt de **Shopify GraphQL Order Edit API** om bundels op te splitsen zonder retour-registraties:

1. `orderEditBegin` — opent een edit sessie
2. `orderEditSetQuantity(quantity: 0)` — verwijdert het bundel line item
3. `orderEditAddVariant` — voegt individuele varianten toe
4. `orderEditCommit(notifyCustomer: false)` — slaat op zonder klant-email
5. `tagsAdd` — voegt tag `bundle-edited` toe voor analytics filtering

## Bestanden

| Bestand | Beschrijving |
|---|---|
| `BundleOrderEditService.php` | Hoofdservice: bundel detectie, opsplitsing, Piqcer verzending |
| `webhook-handler-example.php` | Voorbeeld webhook endpoint voor order/create |

## Wat Lesley moet doen

### 1. Bundel mapping invullen
In `BundleOrderEditService.php`, pas de `loadBundleMapping()` methode aan met jullie echte bundel variant IDs:

```php
'gid://shopify/ProductVariant/12345' => [  // Bundel "Starter Kit"
    ['variantId' => 'gid://shopify/ProductVariant/11111', 'quantity' => 1],  // Product A
    ['variantId' => 'gid://shopify/ProductVariant/22222', 'quantity' => 1],  // Product B
],
```

### 2. Environment variables instellen
```
SHOPIFY_SHOP_DOMAIN=jouw-store.myshopify.com
SHOPIFY_ACCESS_TOKEN=shpat_xxxxx
SHOPIFY_WEBHOOK_SECRET=whsec_xxxxx
PIQCER_API_URL=https://api.piqcer.com/v1/orders
PIQCER_API_KEY=xxxxx
```

### 3. Integreren in bestaande backend
Deze code kan:
- **Vervangen**: de huidige bundel-logica in `src/Service/Shopify/CreateOrder.php`
- **Naast draaien**: als aparte service die de bundel-logica overneemt

### 4. Testen
Test eerst met een test-order in een development store voordat je dit in productie zet.

## Waarom dit werkt
De Shopify Order Edit API (`orderEditBegin` → `orderEditCommit`) behandelt wijzigingen als **edits**, niet als retouren. Er wordt geen `Return` of `Refund` record aangemaakt, waardoor de analytics schoon blijven.
