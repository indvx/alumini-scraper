# Educational Institution & RFP Procurement Intelligence Platform

A Laravel application for discovering educational institutions globally and tracking public RFP/procurement opportunities across institution procurement portals.

This project combines OpenStreetMap-based institution discovery with a scraping and normalization pipeline for procurement platforms such as Bonfire and OpenGov. It stores institutions, platform relationships, and parsed opportunity data in a structured relational model, then exposes search and management interfaces for institutions, procurement platforms, and RFP records.

---

## Features

- Global institution discovery and geocoding:
  - Search countries, states, and cities through OpenStreetMap Nominatim and related lookup flows.
  - Query OpenStreetMap Overpass data to find institutional boundaries and related records.
  - Store and cache location searches, boundaries, and institution metadata.
  - Maintain institution-level attributes including name, type, address, coordinates, and country/state mapping.

- Automated RFP procurement scraping:
  - Strategy-based scraper architecture for multiple procurement platforms.
  - Built-in support for Bonfire and OpenGov scraping flows.
  - Portal discovery using cached database mappings and OpenAI-assisted verification when needed.
  - Normalize raw procurement payloads into structured RFP data models.
  - Filter opportunity data by status, institution, platform, and date range.

- Institution and platform management:
  - Manage institutions, procurement platforms, and their relationship metadata.
  - Track confidence scores, verification timestamps, discovery source, notes, and platform URLs.
  - Import and export institution/platform data through CSV workflows.
  - Search and browse institution and platform records through Laravel views and API-style endpoints.

- RFP search and filtering:
  - Search across RFP title, description, department, institution, and platform fields.
  - Filter by status such as open, past/closed, awarded, and cancelled.
  - Browse results through paginated views.

- Alumni and advancement-oriented matching:
  - Includes an alumni/advancement keyword-based matcher to help identify relevant opportunities.
  - Useful for identifying fundraising, engagement, advancement, reunion, CRM, and related procurement needs.

---

## Technology Stack

- Framework: Laravel 13
- PHP: ^8.3
- Frontend: Blade templates, Tailwind CSS, Vite
- Database: SQLite / MySQL / PostgreSQL via Laravel database config
- External services:
  - OpenStreetMap Nominatim
  - OpenStreetMap Overpass
  - OpenAI API for portal verification and discovery
- Testing and quality:
  - Pest
  - Laravel Pint

---

## Project Structure

```text
app/
├── Console/
│   └── Commands/
│       ├── ScrapeAllRfpsCommand.php
│       ├── ScrapeInstitutionRfpsCommand.php
│       └── ScrapePlatformRfpsCommand.php
├── Data/
│   ├── Rfp/
│   │   ├── DiscoveryResult.php
│   │   ├── RfpData.php
│   │   └── RfpScrapeData.php
│   └── Scraper/
│       └── ScraperResult.php
├── Enums/
│   ├── Rfp/
│   │   └── RfpStatus.php
│   └── Scraper/
│       ├── ScrapeMethod.php
│       └── ScrapeStatus.php
├── Http/
│   └── Controllers/
│       ├── InstitutionController.php
│       ├── InstitutionRFPPlatformController.php
│       ├── LocationLookupController.php
│       ├── LocationSearchController.php
│       ├── RFPsController.php
│       └── RFPsPlatformController.php
├── Models/
│   ├── City.php
│   ├── Country.php
│   ├── Institution.php
│   ├── LocationSearch.php
│   ├── RFP.php
│   ├── RFPsPlatform.php
│   ├── State.php
│   └── User.php
├── Providers/
│   ├── AppServiceProvider.php
│   └── RepositoryServiceProvider.php
├── Repositories/
│   ├── Contracts/
│   └── Eloquent/
├── Services/
│   ├── NominatimService.php
│   ├── OpenAIService.php
│   ├── OverpassService.php
│   └── Rfp/
│       ├── AlumniRfpMatcher.php
│       ├── Contracts/
│       ├── Discovery/
│       ├── Platforms/
│       │   ├── Bonfire/
│       │   └── OpenGov/
│       ├── RfpPersistenceService.php
│       ├── RfpScraperManager.php
│       └── Support/
│           └── ScraperRegistry.php
├── ...
config/
database/
public/
resources/
├── views/
│   ├── institutions/
│   ├── rfps/
│   ├── rfps-platform/
│   └── welcome.blade.php
routes/
└── web.php
```

---

## Installation

1. Clone the repository:

```bash
git clone https://github.com/indvx/alumini-scraper.git
cd alumini-scraper
```

2. Install PHP dependencies:

```bash
composer install
```

3. Install frontend dependencies:

```bash
npm install
```

4. Copy the environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

5. Configure the database and API credentials in `.env`:

```env
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite

OPENAI_API_KEY=your-openai-api-key
```

If using SQLite, create the database file if needed:

```bash
touch database/database.sqlite
```

6. Run database migrations and seeders:

```bash
php artisan migrate --seed
```

7. Build the frontend assets:

```bash
npm run build
```

8. Start the application:

```bash
php artisan serve
```

For local Vite development, you can also run:

```bash
npm run dev
```

---

## Key Routes

The app exposes a set of management and search routes:

- `/locations/countries`
- `/locations/states`
- `/locations/cities`
- `/institutions`
- `/institutions/create`
- `/institutions/search-api/lookup`
- `/rfps`
- `/rfps-platform`

These routes power the institution lookup, RFP browsing, and platform management flows.

---

## Artisan Commands

### Scrape all institutions

```bash
php artisan rfp:scrape-all --type=open
php artisan rfp:scrape-all --type=past --limit=10 --delay=2
php artisan rfp:scrape-all --institution="University of Illinois" --platform="Bonfire"
```

### Scrape a specific institution

```bash
php artisan rfp:scrape-institution "University of Michigan" --platform=Bonfire --type=open
```

### Scrape by platform

```bash
php artisan rfp:scrape-platform Bonfire --type=open
php artisan rfp:scrape-platform OpenGov --type=past
```

---

## Database Model Overview

The application stores the core entities needed for institutional and procurement intelligence:

- `LocationSearch`
- `Institution`
- `RFPsPlatform`
- `Institution` <-> `RFPsPlatform` pivot relationship
- `RFP`

The relationship layer tracks things like discovery confidence, source URL, verification dates, and notes.

---

## Environment Notes

The application expects:

- Laravel application environment configuration
- A database connection
- An OpenAI API key for procurement portal verification

The default `.env.example` includes the standard Laravel settings and exposes the OpenAI key hook via:

```env
OPENAI_API_KEY=your-openai-api-key
```

---

## License

This project is open-source and distributed under the MIT license.
