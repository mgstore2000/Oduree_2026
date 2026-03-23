# Shopify CLI Installatiehandleiding

## Optie 1: Installatie via Node.js/npm (Aanbevolen)

### Stap 1: Node.js installeren

1. Download Node.js LTS van [nodejs.org](https://nodejs.org/)
2. Installeer met standaardinstellingen
3. Controleer installatie:
   ```bash
   node --version
   npm --version
   ```

### Stap 2: Shopify CLI installeren

```bash
npm install -g @shopify/cli@3.92.1 @shopify/theme@3.58.2
```

### Stap 3: Installatie verifiëren

```bash
shopify version
```

Actuele npm packages volgens npm:

- `@shopify/cli`: `3.92.1`
- `@shopify/theme`: `3.58.2`

---

## Optie 2: Installatie via Homebrew (macOS/Linux)

```bash
brew tap shopify/shopify
brew install shopify-cli
```

---

## Optie 3: Windows Package Manager (winget)

```powershell
winget install Shopify.ShopifyCLI
```

---

## Authenticatie

Na installatie, log in op je Shopify store:

```bash
shopify auth login --store mg-e-com.myshopify.com
```

Dit opent een browser voor login. Sluit af met `Ctrl+C` wanneer klaar.

---

## GitHub koppeling met Shopify theme

Belangrijk: de koppeling tussen Shopify en GitHub gebeurt niet via een aparte npm package. Hiervoor gebruik je:

1. Shopify CLI lokaal voor theme development
2. Git lokaal voor commits en push naar GitHub
3. De Shopify GitHub app in Shopify Admin om een branch aan een theme te koppelen

### Vereisten

- Je hebt write access nodig op de GitHub repository
- De repository moet de standaard Shopify theme structuur hebben aan de root
- De Shopify GitHub app moet toegang hebben tot de repository

### Stappen

1. Maak of gebruik een GitHub repository met alleen Shopify theme bestanden aan de root
2. Push je lokale theme code naar een branch op GitHub
3. Open Shopify Admin > Online Store > Themes
4. Klik op `Add theme` > `Connect from GitHub`
5. Kies je GitHub account of organisatie
6. Kies repository en branch
7. Test de koppeling door een kleine wijziging op te slaan in Shopify of GitHub

Shopify maakt daarna automatisch commits naar GitHub wanneer theme code via de Shopify admin wordt aangepast.

---

## Troubleshooting

### "command not found" fout

**Windows:** Voeg npm global path toe aan PATH:
```powershell
# Voeg toe aan je PowerShell profile of voer uit:
$env:Path += ";$env:APPDATA\npm"
```

### macOS/Linux: Permission errors

Gebruik `sudo` of fix npm permissions:
```bash
sudo npm install -g @shopify/cli @shopify/theme
```

---

## Projectstructuur

Na authenticatie kun je thema's beheren in deze map:

```
Oduree ROOT/
├── .windsurf/
│   └── workflows/
│       └── shopify-cli.md    # Workflow commands
├── layout/
├── templates/
├── sections/
├── snippets/
├── assets/
├── config/
├── locales/
└── SHOPIFY_CLI_INSTALLATION.md  # Deze handleiding
```

---

## Snelle Start Commands

| Taak | Command |
|------|---------|
| Thema pullen | `shopify theme pull --store mg-e-com.myshopify.com` |
| Dev server starten | `shopify theme dev --store mg-e-com.myshopify.com` |
| Thema pushen | `shopify theme push --store mg-e-com.myshopify.com` |
| Thema's lijst | `shopify theme list --store mg-e-com.myshopify.com` |
| GitHub branch koppelen | `Shopify Admin > Online Store > Themes > Add theme > Connect from GitHub` |

---

## Documentatie

- [Shopify CLI Docs](https://shopify.dev/docs/api/shopify-cli)
- [Theme Commands](https://shopify.dev/docs/api/shopify-cli/theme)
- [Shopify GitHub integration for themes](https://shopify.dev/docs/storefronts/themes/tools/github)
