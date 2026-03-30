# CRO Analyse - Oduree Productpagina (Duo Set)

**Pagina:** https://oduree.nl/products/duo-set-aph
**Datum:** 25 maart 2026

---

## Wat goed is (behouden!)

- **Bundel-selector met tiers** — 1, 2, 5, 10 stuks met duidelijke labels ("MEEST GEKOZEN", "BESTE DEAL")
- **Gratis gifts per tier** — Goede incentive om meer te bestellen
- **Prijsanker** — ~~€45,80~~ → €35,90 (duidelijke korting zichtbaar)
- **Trust badges** — Vandaag besteld morgen in huis, Niet tevreden? Geld terug, Gratis verzending €50+, Klarna
- **FAQ sectie** — Beantwoordt de meest gestelde vragen
- **Trustpilot integratie** — 100.000+ klanten (social proof)
- **Garantie sectie** — "Bevalt de geur toch niet? Geen probleem!"
- **Celebrity mentions** — Eljero Elia, Josylvio

---

## PRIORITEIT 1: Bugs & fouten (direct fixen)

### 1.1 ❌ "Translation missing" errors zichtbaar op de pagina
**Locatie:** Footer/onderaan de pagina
**Probleem:** Klanten zien "Translation missing: nl.accessibility.refresh_page" en "Translation missing: nl.accessibility.link_messages.new_window"
**Impact:** Onprofessioneel, verlaagt vertrouwen
**Oorzaak:** Waarschijnlijk een Shopify app die vertalingen mist
**Fix:** Check welke app deze strings genereert. Voeg de ontbrekende keys toe aan `nl.json` of update de app.

### 1.2 ✅ "You may also like" is Engels (GEFIXT)
**Locatie:** Product recommendations sectie
**Probleem:** De rest van de site is Nederlands, maar deze header was Engels
**Fix:** Aangepast naar "Dit vind je misschien ook leuk" in `product.json`

### 1.3 ⚠️ Handleiding tekst klopt niet
**Locatie:** Handleiding sectie
**Probleem:** Er staat "In 3 simpele stappen ruikt je **huis** heerlijk!" maar dit is een **auto**parfum
**Fix:** Aanpassen in de Shopify admin (productbeschrijving of metafield)

### 1.4 ⚠️ Cart drawer toont "Voeg nog 0 toe"
**Locatie:** Winkelwagen slide-out
**Probleem:** Progress bar tekst toont "Voeg nog 0 toe en ontvang GRATIS verzending!" — de "0" is een bug
**Fix:** Check de cart drawer logic voor de gratis verzending drempel berekening

---

## PRIORITEIT 2: Conversie-verhogende verbeteringen

### 2.1 🔴 Geen reviews/sterren bij het product
**Impact:** HOOG
**Probleem:** Jullie noemen "100.000+ tevreden klanten" en hebben Trustpilot, maar er zijn geen sterren of review-count zichtbaar bij de producttitel of prijs.
**Fix:** Voeg een sterren-rating toe (bijv. ★★★★★ 4.8/5 - 2.340 reviews) direct onder de producttitel. Dit kan via:
- Shopify product metafields voor sterren
- Trustpilot widget/snippet
- Judge.me of Loox reviews app

### 2.2 🔴 Geen urgency/scarcity elementen
**Impact:** HOOG
**Probleem:** Er is geen reden om NU te kopen in plaats van later.
**Mogelijke fixes:**
- "Vandaag besteld, morgen in huis" prominenter boven de CTA
- "🔥 432 mensen kochten dit vandaag" (social proof counter)
- Voorraad indicator: "Nog maar 12 op voorraad"
- Timer bij een actie/korting

### 2.3 🟡 Benefits sectie is te zwak
**Impact:** MEDIUM
**Probleem:** Slechts 2 generieke voordelen worden getoond
**Fix:** Uitbreiden naar 4-6 specifieke USPs, bijvoorbeeld:
- ✅ Ruikt naar je favoriete parfum
- ✅ Geur blijft 4-6 weken hangen
- ✅ Past in elke auto
- ✅ Niet tevreden? Geld terug!
- ✅ Gratis verzending vanaf €50
- ✅ 100.000+ tevreden klanten

### 2.4 🟡 Geen "meest gekozen geur" indicatie
**Impact:** MEDIUM
**Probleem:** Bij 20 geuren weten klanten niet welke populair zijn. Keuzestress = verlaten pagina.
**Fix:** Voeg labels toe bij populaire geuren:
- "🏆 Best seller" bij Savage
- "❤️ Populair" bij Million
- Of sorteer geuren op populariteit

### 2.5 🟡 Prijs per stuk is niet prominent genoeg
**Impact:** MEDIUM
**Probleem:** €17,95/autoparfum staat er wel, maar de BESPARING is niet duidelijk
**Fix:** Toon expliciet: "Je bespaart €9,90!" of "22% korting" naast de prijs

### 2.6 🟡 CTA button tekst kan beter
**Impact:** MEDIUM
**Probleem:** Standaard "Aan winkelwagen toevoegen" is generiek
**Fix:** Overwegen: "Bestel nu - Morgen in huis" of "Voeg toe aan winkelwagen - €35,90"

---

## PRIORITEIT 3: Geavanceerde CRO

### 3.1 Geen cross-sell/upsell in cart
**Probleem:** Na toevoegen aan winkelwagen is er geen suggestie voor aanvullende producten
**Fix:** Voeg in de cart drawer toe:
- "Maak je set compleet met..." (huisparfum, geurstokjes)
- "Voeg een Gift Box toe voor €9,95"

### 3.2 Geen social proof dicht bij de CTA
**Probleem:** Trustpilot en "100.000+" staan ver weg van de koop-button
**Fix:** Voeg direct onder de CTA een kleine regel toe:
- "★★★★★ Beoordeeld met 4.8/5 door 2.340 klanten"

### 3.3 Geen bundle comparison tabel
**Probleem:** Klanten moeten zelf uitrekenen welke bundel de beste deal is
**Fix:** Toon een vergelijkingstabel:

| | 1 stuk | 2 stuks | 5 stuks | 10 stuks |
|---|---|---|---|---|
| Prijs per stuk | €19,95 | €17,95 | €15,95 | €13,95 |
| Korting | 0% | 10% | 20% | 30% |
| Gratis gifts | 1x sample | 2x sample | €34,85 | €84,75 |

### 3.4 Exit-intent popup
**Probleem:** Bezoekers die weggaan worden niet geconverteerd
**Fix:** Exit-intent popup met:
- "Wacht! Krijg 10% korting op je eerste bestelling"
- E-mail capture voor remarketing

### 3.5 Mobile specifiek
**Probleem:** 20 geuren op mobile = veel scrollen
**Fix:**
- Collapsed/accordion view voor geuren op mobile
- Sticky "Toevoegen" button onderaan het scherm (al aanwezig via sticky add-to-cart!)
- Swipeable geur-carousel

---

## Quick Wins (< 1 uur werk)

| # | Actie | Impact | Effort |
|---|---|---|---|
| 1 | Voeg ★★★★★ rating toe onder producttitel | Hoog | Laag |
| 2 | Fix "huis" → "auto" in handleiding tekst | Laag | 5 min |
| 3 | Voeg "Je bespaart €X" toe bij prijs | Medium | Laag |
| 4 | Voeg "Best seller" label toe bij populaire geuren | Medium | Laag |
| 5 | Voeg "X mensen kochten dit vandaag" toe | Hoog | Medium |
| 6 | Fix translation missing errors | Laag | Medium |

---

## Geschatte impact

Als jullie de top 3 verbeteringen implementeren (reviews bij product, urgency elementen, betere benefits), verwacht ik een **conversie-verhoging van 10-25%** op deze productpagina, gebaseerd op standaard e-commerce benchmarks.

De bundel-selector en gratis gifts zijn al sterke converters — de pagina mist voornamelijk **social proof dicht bij de CTA** en **urgency**.
