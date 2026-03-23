# Oduree Shopify Theme

Shopify theme project for `mg-e-com.myshopify.com`.

## Store

- Store: `mg-e-com.myshopify.com`

## Project structure

```text
assets/
blocks/
config/
layout/
locales/
sections/
snippets/
templates/
```

## Shopify CLI

Install Shopify CLI:

```bash
npm install -g @shopify/cli@3.92.1 @shopify/theme@3.58.2
```

Run theme commands:

```bash
shopify theme pull --store mg-e-com.myshopify.com --path .
shopify theme dev --store mg-e-com.myshopify.com
shopify theme push --store mg-e-com.myshopify.com
```

## Git setup

Initialize Git:

```bash
git init
git add .
git commit -m "Initial Shopify theme setup"
```

Set your Git identity if needed:

```bash
git config --global user.name "Your Name"
git config --global user.email "you@example.com"
```

## GitHub

After creating or connecting a remote repository:

```bash
git remote add origin YOUR_GITHUB_REPO_URL
git branch -M main
git push -u origin main
```

## Notes

- The Shopify GitHub connection for themes is managed in Shopify Admin.
- Path: `Online Store > Themes > Add theme > Connect from GitHub`
