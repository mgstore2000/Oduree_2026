# Oduree - Architectuur Plan: Bundels, Abonnementen, Kortingen & Gifts

## Jullie vereisten (samengevat)

| # | Vereiste | Voorbeeld |
|---|---|---|
| 1 | **Custom bundels** | Klant stelt zelf een bundel samen (kies geuren, aantallen) |
| 2 | **Vaste bundels** | Voorgedefinieerde bundels (bijv. "Starter Kit") |
| 3 | **Abonnementen** | Klant kan eenmalig OF als abonnement bestellen |
| 4 | **Volumekorting** | Hoe meer je bestelt, hoe meer korting |
| 5 | **Abonnementskorting** | Extra korting als je een abonnement kiest |
| 6 | **Gratis gifts** | Hoe meer je bestelt, hoe meer gratis producten |

---

## Oplossing per vereiste

### 1. Custom bundels (klant stelt zelf samen)

**Probleem:** Shopify heeft geen native "bouw je eigen bundel" functionaliteit.

**Oplossing: Custom Bundle Builder (Theme App Extension + Shopify Functions)**

- **Frontend:** Een bundle builder pagina in je theme (of App Block) waar klanten:
  - Geuren kiezen
  - Aantallen per geur selecteren
  - Zien hoe de prijs verandert

- **Backend:** Gebruik **Cart Transform (Shopify Function)** om de bundel bij checkout automatisch om te zetten naar losse line items. Hierdoor:
  - ✅ Geen order edit achteraf nodig
  - ✅ Geen retour-registraties
  - ✅ Piqcer ontvangt direct de losse producten

**Alternatief:** Apps zoals **Rebundle**, **Bundles.app**, of **PickyStory** doen dit out-of-the-box.

---

### 2. Vaste bundels (voorgedefinieerd)

**Oplossing: Shopify Bundles app (gratis, van Shopify zelf)**

- Maak bundel-producten aan in Shopify Admin
- Componenten worden automatisch bijgehouden
- Bij checkout worden componenten meegestuurd
- ✅ Geen order edit nodig

---

### 3. Abonnementen

**Probleem:** Shopify ondersteunt abonnementen alleen via apps.

**Oplossing: Shopify Subscriptions app (gratis) of Recharge**

| App | Voordeel | Nadeel |
|---|---|---|
| **Shopify Subscriptions** (gratis) | Gratis, native integratie | Minder flexibel |
| **Recharge** | Zeer flexibel, bundels + subscriptions | Betaald (~$99/mo) |
| **Bold Subscriptions** | Goede bundel-support | Betaald |

**Aanbeveling:** Als jullie bundels + abonnementen willen combineren, is **Recharge** de beste optie. Het ondersteunt:
- Abonnementen op individuele producten
- Abonnementen op bundels
- Klant kan bundel-inhoud wijzigen per levering

---

### 4. Volumekorting (hoe meer je koopt, hoe meer korting)

**Oplossing: Shopify Functions - Discount Function**

Dit is een custom Shopify Function die automatisch korting berekent op basis van quantity:

```
Voorbeeld staffelkorting:
- 1-2 producten:  0% korting
- 3-5 producten:  10% korting
- 6-10 producten: 15% korting
- 11+ producten:  20% korting
```

**Hoe het werkt:**
1. Maak een Shopify Function (type: `discount`)
2. De functie checkt het totaal aantal items in de cart
3. Past automatisch de juiste korting toe bij checkout

**Alternatief:** Apps zoals **Shopify Discount Plus**, **Bold Discounts**, of **Quantity Breaks & Discounts**.

---

### 5. Abonnementskorting

**Oplossing: Selling Plan + Discount combinatie**

Shopify's **Selling Plans** (onderdeel van subscriptions) ondersteunen native korting:
- Bijv. 15% korting bij abonnement
- Dit wordt ingesteld bij het aanmaken van het selling plan
- De korting is zichtbaar op de productpagina (eenmalig vs. abonnement prijs)

Dit werkt automatisch als je een subscription app gebruikt (Recharge, Shopify Subscriptions).

---

### 6. Gratis gifts (hoe meer je bestelt, hoe meer gratis producten)

**Oplossing: Shopify Functions - Cart Transform of Discount Function**

Twee opties:

**Optie A: Cart Transform Function**
- Checkt het totaal aantal producten of orderbedrag
- Voegt automatisch gratis producten toe aan de cart
- Voorbeeld:
  - 3+ producten → 1 gratis sample
  - 6+ producten → 2 gratis samples + travel size
  - 10+ producten → 3 gratis samples + full size gift

**Optie B: Automatic Discount (Shopify native)**
- Shopify Admin → Discounts → Create "Buy X Get Y"
- Minder flexibel maar geen code nodig

**Alternatief:** Apps zoals **Gift Box**, **Free Gifts BOGO**, of **Monk Free Gift Offers**.

---

## Aanbevolen architectuur

```
┌─────────────────────────────────────────────┐
│                 FRONTEND                     │
│                                             │
│  Theme + App Blocks                         │
│  ├── Bundle Builder pagina (custom UI)      │
│  ├── Abonnement toggle (selling plans)      │
│  └── Volumekorting tabel (zichtbaar)        │
└─────────────┬───────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────┐
│              CHECKOUT                        │
│                                             │
│  Shopify Functions:                         │
│  ├── Cart Transform    → bundel opsplitsen  │
│  ├── Discount Function → volumekorting      │
│  └── Cart Transform    → gratis gifts       │
│                                             │
│  Selling Plans:                             │
│  └── Abonnementskorting (automatisch)       │
└─────────────┬───────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────┐
│            ORDER CREATED                     │
│                                             │
│  Order bevat AL de losse varianten          │
│  (GEEN order edit meer nodig!)              │
│  ├── Individuele producten (uit bundel)     │
│  ├── Korting al toegepast                   │
│  ├── Gratis gifts al toegevoegd             │
│  └── Subscription info aanwezig             │
└─────────────┬───────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────┐
│             FULFILLMENT                      │
│                                             │
│  Webhook → Piqcer                           │
│  (Geen order edit nodig = geen retouren)    │
└─────────────────────────────────────────────┘
```

---

## Apps vs Custom Code: Wat is beter?

| Aanpak | Voordeel | Nadeel | Geschikt als... |
|---|---|---|---|
| **Alles custom (Shopify Functions)** | Volledige controle, geen app kosten | Veel development tijd, onderhoud | Je een developer hebt (Lesley) |
| **Apps combineren** | Snel live, bewezen oplossingen | Maandelijkse kosten, minder flexibel | Je snel resultaat wilt |
| **Hybride** | Beste van beide | Complexer | Je specifieke vereisten hebt |

**Aanbeveling voor jullie:** Start met een **hybride aanpak**:
1. **Recharge** voor abonnementen + abonnementskorting (bewezen, betrouwbaar)
2. **Custom Shopify Function** voor volumekorting en gratis gifts (Lesley kan dit bouwen)
3. **Custom bundle builder** in het theme of een app zoals Rebundle

---

## Actieplan

### Fase 1: Quick wins (1-2 weken)
- [ ] Shopify Bundles app installeren voor vaste bundels
- [ ] Shopify native "Buy X Get Y" discount instellen voor gratis gifts
- [ ] Volumekorting instellen via Shopify discount codes of een app

### Fase 2: Abonnementen (2-4 weken)
- [ ] Recharge of Shopify Subscriptions installeren en configureren
- [ ] Selling plans aanmaken met abonnementskorting
- [ ] Productpagina's aanpassen (eenmalig vs. abonnement toggle)

### Fase 3: Custom bundle builder (4-8 weken)
- [ ] Bundle builder pagina ontwerpen (UX/UI)
- [ ] Cart Transform Shopify Function bouwen (Lesley)
- [ ] Testen met Piqcer integratie
- [ ] Huidige order edit logica uitfaseren

### Fase 4: Geavanceerde kortingen (2-4 weken)
- [ ] Custom Discount Function bouwen voor staffelkorting
- [ ] Gratis gifts via Cart Transform
- [ ] Combinatieregels instellen (volume + abonnement + gifts)

---

## Vragen om te bespreken met Lesley

1. Welke subscription app gebruiken jullie nu, of moet dit nog gekozen worden?
2. Hoeveel verschillende bundel-configuraties zijn er?
3. Kan de huidige backend (PHP) Shopify Functions draaien, of is dat een apart project?
4. Wat is het budget voor apps (Recharge = ~$99/mo)?
5. Wat is de prioriteit: eerst abonnementen of eerst bundels?
