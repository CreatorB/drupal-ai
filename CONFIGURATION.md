# Configuration Guide

## Multisite mapping

The project uses domain-based multisite routing in `web/sites/sites.php`.

- `primary.ddev.site` -> primary site
- `site1.ddev.site` -> multisite sample 1 (branded subtheme `site1_theme`)
- `site2.ddev.site` -> multisite sample 2 (branded subtheme `site2_theme`)

## Shared settings approach

Each site has its own `settings.php`, but common logic lives in `web/sites/settings.shared.php`.

This keeps:

- shared environment variable loading in one place
- separate database names per site
- separate config sync directories under `config/`
- the OpenCode API settings outside committed Drupal config

## Environment variables

Use `.env` for local work and inject the same values via hosting secrets in production.

Required values:

- `OPENCODE_API_KEY`
- `OPENCODE_API_ENDPOINT`
- `OPENCODE_MODEL`
- `OPENCODE_FALLBACK_MODEL`

Optional values:

- `AI_PROVIDER_NAME`
- `AI_PROVIDER_SITE_URL`
- `AI_PROVIDER_APP_NAME`
- `LIBRETRANSLATE_ENDPOINT`
- `DRUPAL_DB_HOST`
- `DRUPAL_DB_PORT`
- `DRUPAL_DB_USER`
- `DRUPAL_DB_PASSWORD`
- `DRUPAL_DB_PRIMARY`
- `DRUPAL_DB_SITE1`
- `DRUPAL_DB_SITE2`

## Free provider options for demo

The AI search module now supports any OpenAI-compatible provider base URL.

- `OpenCode`: `https://api.opencode.dev/v1`
- `OpenRouter`: `https://openrouter.ai/api/v1`
- `Groq`: `https://api.groq.com/openai/v1`

Recommended demo fallback if OpenCode is unavailable:

```env
OPENCODE_API_KEY=your_openrouter_key
OPENCODE_API_ENDPOINT=https://openrouter.ai/api/v1
OPENCODE_MODEL=meta-llama/llama-3.3-70b-instruct:free
OPENCODE_FALLBACK_MODEL=mistralai/mistral-small-3.1-24b-instruct:free
AI_PROVIDER_NAME=OpenRouter
AI_PROVIDER_SITE_URL=https://primary.ddev.site
AI_PROVIDER_APP_NAME=Drupal AI Multisite Demo
```

After changing `.env`, run:

```bash
ddev drush --uri=https://primary.ddev.site cr
```

## Getting a free API key

Fastest path for this project:

- `OpenRouter`: create an account at `https://openrouter.ai/`, generate an API key, and use the `.env` example above.
- `Groq`: create an account at `https://console.groq.com/`, generate an API key, and use `https://api.groq.com/openai/v1` as the endpoint.

Once your key is added:

```bash
ddev restart
ddev drush --uri=https://primary.ddev.site cr
ddev drush --uri=https://primary.ddev.site php:eval '$result = \Drupal::service("drupal_ai_search.opencode_api")->testConnection(); var_export($result);'
```
