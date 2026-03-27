# Installation Guide

## Recommended root structure

This project keeps the showcase setup lean:

- `web/` contains the Drupal docroot and all shared code.
- `config/` contains per-site configuration sync directories.
- `.ddev/` supports the local multisite demo environment.

## 1. Prepare environment

1. Install DDEV and Docker Desktop.
2. Copy `.env.example` to `.env` and add your OpenCode API key.
3. Make sure PHP 8.2 and Composer are available inside DDEV.

## 2. Install dependencies

```bash
ddev start
ddev composer install
```

## 3. Install each site

```bash
ddev drush --uri=https://primary.ddev.site site:install standard --db-url=mysql://db:db@db/primary_db --site-name="Drupal AI Primary" --account-name=admin --account-pass="change-me-admin-pass" --locale=en -y
ddev drush --uri=https://site1.ddev.site site:install standard --db-url=mysql://db:db@db/site1_db --site-name="Drupal AI Site 1" --account-name=admin --account-pass="change-me-admin-pass" --locale=en -y
ddev drush --uri=https://site2.ddev.site site:install standard --db-url=mysql://db:db@db/site2_db --site-name="Drupal AI Site 2" --account-name=admin --account-pass="change-me-admin-pass" --locale=en -y
```

## 4. Enable the planned features

```bash
ddev drush --uri=https://primary.ddev.site en admin_toolbar admin_toolbar_tools pathauto metatag webform search_api search_api_db paragraphs field_group token views_infinite_scroll language locale content_translation config_translation drupal_ai_search drupal_personalized_widget -y
ddev drush --uri=https://primary.ddev.site theme:enable sbf_theme -y
ddev drush --uri=https://primary.ddev.site config:set system.theme default sbf_theme -y
ddev drush --uri=https://site1.ddev.site theme:enable olivero -y
ddev drush --uri=https://site2.ddev.site theme:enable olivero -y
```

## 5. Finish the client demo setup

1. Add sample articles, events, and taxonomy terms.
2. Place the personalized recommendations block.
3. Configure AI settings at `/admin/config/services/drupal-ai-search`.
4. Build the homepage layout with blocks/views matching the SBF-inspired sections.
