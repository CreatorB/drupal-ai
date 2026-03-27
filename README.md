# Drupal AI Multisite Platform

A Drupal 11 multisite platform with AI-powered natural language search, a cross-site search hub, a floating AI chat assistant, and personalized content recommendations.

## Overview

This project delivers:

- Domain-based **Drupal 11 multisite** with a primary site plus two satellite sites
- **Primary finance hub** with a premium SBF-inspired presentation layer
- **Site 1 technology portal** with AI-powered search and IT-focused seeded content
- **Site 2 technology comparison portal** with the same seeded content but **keyword-only** search
- **Cross-site AI search** on the primary site, combining local content with an external index
- **Floating AI chat widget** that uses the same search engine through a JSON endpoint
- **Personalized recommendations** based on reading behavior
- **Multilingual-ready** content architecture for English and Indonesian

## Current Demo Story

The current demo is intentionally structured to show a clear comparison:

- **Primary**: finance and business content, plus a search hub that can surface external SBF and Site 1 technology content
- **Site 1**: AI-powered technology publishing with 50+ seeded IT articles
- **Site 2**: keyword-only technology publishing using the same IT dataset as Site 1

This lets the demo prove three things:

- one shared codebase can drive multiple branded sites
- AI search behaves differently from classic keyword matching
- a central site can search across multiple sources from a single interface

## Core Features

### Multi-Source AI Search

The primary site supports natural language search powered by an OpenAI-compatible provider. Search results combine:

- **Local Drupal nodes** from the primary site
- **External SBF references** stored in `external_content_index`
- **External Site 1 references** stored in `external_content_index`

Result cards support:

- source badges
- external-link indicators
- safe new-tab behavior for external content
- AI failure fallback to keyword mode

### AI Chat Widget

The primary theme includes a floating AI chat widget in the lower-left corner. It:

- opens from a floating bubble
- sends the user prompt to `/ai-search-api`
- reuses the same parsing and ranking pipeline as `/ai-search`
- returns summary text plus relevant content links

### Personalized Recommendations

The recommendation engine tracks reading behavior and suggests related content based on:

- page views
- time spent
- scroll depth
- unread content preference

## Architecture

### Sites

| Site | URL | Theme | Role |
|------|-----|-------|------|
| Primary | `https://primary.ddev.site` | `sbf_theme` | Finance hub + cross-site AI search |
| Site 1 | `https://site1.ddev.site` | `site1_theme` | AI-powered technology site |
| Site 2 | `https://site2.ddev.site` | `site2_theme` | Keyword-only technology comparison site |

### Custom Modules

| Module | Purpose |
|--------|---------|
| `drupal_ai_search` | Natural language search, external index integration, `/ai-search-api`, AI fallback handling |
| `drupal_basic_search` | Keyword-only search experience for Site 2 |
| `drupal_personalized_widget` | Reading-history tracking and personalized recommendations |

### Important Scripts

| Script | Purpose |
|--------|---------|
| `scripts/setup_primary_plan.php` | Creates content model, fields, and taxonomy |
| `scripts/seed_it_content.php` | Seeds 50+ IT articles plus events, press releases, and speeches |
| `scripts/seed_external_index.php` | Seeds the external cross-site search index |
| `scripts/seed_demo_content.php` | Original demo content seeding helper |

## Search Flow

### Primary AI Search

1. User enters a natural-language query
2. `QueryParserService` interprets intent with AI, then falls back to rules if needed
3. `SearchExecutorService` queries:
   - local Drupal content
   - external content index
4. Results are merged and ranked
5. The UI shows source badges and fallback/error state when relevant

### Site 2 Keyword Search

1. User enters a query
2. `drupal_basic_search` performs keyword/token matching
3. Results are returned without AI parsing

This is useful for live comparison against Site 1.

## Chat Widget Flow

1. User clicks the floating chat bubble
2. User sends a question
3. `chat-widget.js` calls `/ai-search-api?q=...`
4. The backend returns:
   - parsed summary
   - AI status
   - ranked results
5. The widget renders links back into Drupal or to external sources

## Content Model

| Content Type | Key Fields |
|--------------|------------|
| Article | Title, Body, Category, Tags, Summary |
| Event | Title, Description, Event Date, Location, Event Type |
| Press Release | Title, Body, Release Date, Contact |
| Speech / Commentary | Title, Body, Speaker, Occasion, Date |

## Local Setup

### Prerequisites

- DDEV
- Docker Desktop
- Git
- Composer
- An OpenAI-compatible API key for AI search

### Start

```bash
ddev start
ddev composer install
```

### Useful Commands

```bash
ddev drush --uri=primary.ddev.site cr
ddev drush --uri=site1.ddev.site cr
ddev drush --uri=site2.ddev.site cr
```

### Windows Helpers

```bat
start-demo.cmd
stop-demo.cmd
stop-all-ddev.cmd
share-all-sites.cmd
configure-tunnels.cmd
```

## Demo Proof Queries

### Primary

- `green bonds` -> local finance content
- `SBF employment` -> external SBF result
- `kubernetes` -> external Site 1 result

### Site 1

- `kubernetes`
- `cloud cost`
- `AI in production`

### Site 2

- `kubernetes`
- `cloud cost`
- `devops`

Use the same query on Site 1 and Site 2 to show AI parsing versus keyword matching.

## Configuration

AI provider settings are environment-driven and configurable through Drupal admin.

Key variables:

- `OPENCODE_API_KEY`
- `OPENCODE_API_ENDPOINT`
- `OPENCODE_MODEL`
- `OPENCODE_FALLBACK_MODEL`
- `AI_PROVIDER_NAME`

Admin UI:

- `/admin/config/services/ai-integration`

## Additional Documentation

- [INSTALL.md](INSTALL.md)
- [CONFIGURATION.md](CONFIGURATION.md)
- [DEMO-GUIDE.md](DEMO-GUIDE.md)
- [PERSONAL-GUIDE-EN.md](PERSONAL-GUIDE-EN.md)
- [PERSONAL-GUIDE-ID.md](PERSONAL-GUIDE-ID.md)

## License

Proprietary. All rights reserved.
