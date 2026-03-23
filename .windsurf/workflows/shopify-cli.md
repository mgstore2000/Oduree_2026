---
description: Shopify CLI theme operations - pull, rebase, and push
---

# Shopify CLI Theme Workflow

## Prerequisites

1. Shopify CLI installed (see SHOPIFY_CLI_INSTALLATION.md)
2. Authenticated with `shopify auth`
3. Theme exists in your Shopify store

## Initial Setup (One-time)

1. **Login to Shopify**
   ```bash
   shopify auth login --store mg-e-com.myshopify.com
   ```

2. **Initialize theme in this folder** (if starting fresh)
   ```bash
   shopify theme init
   ```

## Daily Operations

### Pull Theme from Shopify

Download the live theme or a specific theme to your local folder:

```bash
# Pull live theme
shopify theme pull --store mg-e-com.myshopify.com

# Pull specific theme (get theme ID from Shopify admin)
shopify theme pull --theme THEME_ID --store mg-e-com.myshopify.com

# Pull to specific folder
shopify theme pull --path ./theme --store mg-e-com.myshopify.com
```

### Rebase Strategy

Before pushing changes, always pull latest to avoid conflicts:

```bash
# 1. Stash your local changes (optional but recommended)
git add .
git stash

# 2. Pull latest theme from Shopify
shopify theme pull --store mg-e-com.myshopify.com

# 3. Apply your changes back
git stash pop

# 4. Resolve any conflicts manually
```

### Push Theme to Shopify

Upload your local changes to Shopify:

```bash
# Push to live theme (WARNING: affects live store)
shopify theme push --store mg-e-com.myshopify.com

# Push to unpublished theme (RECOMMENDED for development)
shopify theme push --unpublished --theme "Development Theme" --store mg-e-com.myshopify.com

# Push specific files only
shopify theme push --only sections/*.liquid --store mg-e-com.myshopify.com

# Push with nodelete (keeps remote files not in local)
shopify theme push --nodelete --store mg-e-com.myshopify.com
```

## Development Server

Preview changes locally before pushing:

```bash
# Start development server
shopify theme dev --store mg-e-com.myshopify.com

# Dev server on specific port
shopify theme dev --store mg-e-com.myshopify.com --port 9292
```

## Useful Commands Reference

| Command | Description |
|---------|-------------|
| `shopify theme list` | List all themes in store |
| `shopify theme check` | Validate theme code |
| `shopify theme package` | Create .zip of theme |
| `shopify theme delete` | Delete a theme |

## GitHub Connection

The GitHub connection for themes is managed in Shopify Admin, not through an extra npm package.

1. Push your theme code to a GitHub repository
2. Open Shopify Admin > Online Store > Themes
3. Click `Add theme` > `Connect from GitHub`
4. Select repository and branch
5. Verify the theme appears in the theme library with branch info

## Environment Variables (Optional)

Set these in your shell to avoid typing `--store` every time:

```bash
# Windows PowerShell
$env:SHOPIFY_FLAG_STORE = "mg-e-com.myshopify.com"

# Windows CMD
set SHOPIFY_FLAG_STORE=mg-e-com.myshopify.com
```
