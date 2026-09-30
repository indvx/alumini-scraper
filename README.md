# Educational Institution & RFP Procurement Intelligence Platform

A Laravel platform for discovering educational institutions (Schools, Colleges, Universities, Kindergartens) globally using OpenStreetMap APIs and scraping procurement RFPs from public bidding platforms (Bonfire, OpenGov, etc.).

---

## 🚀 Features & Capabilities

- **Global Institution Ingestion & Geocoding**:
  - Search location areas (Country, State, City) via **OpenStreetMap Nominatim API**.
  - Query **Overpass API** for administrative boundary university data with strict duplicate removal, coordinate distance validation, and name-matching logic.
  - Store and cache locations, boundary search entries, and institution records.

- **Automated RFP Procurement Scraping**:
  - Extensible **Strategy Pattern** architecture with modular scrapers (`Bonfire`, `OpenGov`).
  - Automated discovery of institution procurement portal URLs (Database cache fallback with OpenAI verification).
  - Normalization of raw RFP payloads into structured DTOs (`RfpData`, `RfpScrapeData`, `ScraperResult`).
  - Support for filtering by date range, opportunity status (Open, Closed/Past, Awarded, Cancelled), and institution/platform filters.

- **Institution & RFP Platform Management**:
  - Full relational mapping between Institutions and RFP Platforms with pivot metadata (confidence scores, status, verification dates, notes, and source URLs).
  - Bulk CSV import for linking institution procurement portal URLs.
  - Streaming CSV export for institutions, platform lists, and search queries.

- **Future-Ready Alumni RFP Matcher**:
  - Built-in `AlumniRfpMatcher` service to analyze RFP titles, descriptions, and departments against keyword taxonomies (Alumni CRM, Engagement, Advancement, Fundraising, Reunion, Mentorship, Blackbaud, HiveBrite, Toucantech, etc.).

---

## 🛠️ Technology Stack

- **Framework**: Laravel 12 (PHP 8.5)
- **Database**: SQLite / MySQL / PostgreSQL
- **Frontend**: Blade Templates, Tailwind CSS, Alpine.js
- **Services & APIs**:
  - OpenStreetMap Nominatim Geocoding API
  - OpenStreetMap Overpass Interpreter API
  - OpenAI API (OpenAI PHP SDK for portal URL verification)
- **Code Quality**: Laravel Pint, Pest Testing Suite

---

## 📂 Architecture Overview

```
app/
├── Console/Commands/
│   ├── ScrapeAllRfpsCommand.php           # rfp:scrape-all
│   ├── ScrapeInstitutionRfpsCommand.php   # rfp:scrape-institution
│   └── ScrapePlatformRfpsCommand.php      # rfp:scrape-platform
├── Data/
│   ├── Rfp/                               # DTOs: RfpData, RfpScrapeData, DiscoveryResult
│   └── Scraper/                           # ScraperResult
├── Enums/
│   ├── Rfp/RfpStatus.php                  # OPEN, PAST, CLOSED, AWARDED, CANCELLED, ALL
│   └── Scraper/                           # ScrapeMethod, ScrapeStatus
├── Http/Controllers/
│   ├── InstitutionController.php          # CRUD, CSV Export & Search API
│   ├── InstitutionRFPPlatformController.php # Pivot relationship management & CSV import
│   ├── LocationLookupController.php       # Dynamic Country/State/City lookups
│   ├── LocationSearchController.php       # OSM location search & Overpass ingestion
│   ├── RFPsController.php                 # RFP directory & web scrape trigger
│   └── RFPsPlatformController.php         # Platform CRUD & CSV Export
├── Models/
│   ├── Institution.php, RFP.php, RFPsPlatform.php, LocationSearch.php, Country.php, State.php, City.php
├── Repositories/
│   ├── Contracts/                         # Repository interfaces
│   └── Eloquent/                          # Institution, RFPsPlatform, LocationSearch repos
└── Services/
    ├── NominatimService.php               # OSM Geocoding
    ├── OverpassService.php                # Overpass API parser & validation
    ├── OpenAIService.php                  # OpenAI API client
    └── Rfp/
        ├── AlumniRfpMatcher.php           # Alumni/Advancement RFP keyword matcher
        ├── RfpPersistenceService.php      # RFP model updater & database persistence
        ├── RfpScraperManager.php          # Main scraping orchestration manager
        ├── Discovery/                     # Portal URL discovery service
        ├── Support/ScraperRegistry.php    # Scraper registry locator
        └── Platforms/
            ├── Bonfire/                   # Bonfire API Strategy, Normalizer, Config
            └── OpenGov/                   # OpenGov API Strategy, Normalizer, Config
```

---

## ⚙️ Installation & Setup

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd alumini-scraper
   ```

2. **Install PHP & Node dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment Variables**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Set up your database and OpenAI API key in `.env`:
   ```env
   DB_CONNECTION=sqlite
   OPENAI_API_KEY=your-openai-api-key
   ```

4. **Run Database Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

5. **Build Assets & Launch Server**:
   ```bash
   npm run build
   php artisan serve
   ```

---

## 💻 Artisan Commands

### 1. Scrape All Institutions
Scrape RFPs across all institutions and platforms:
```bash
php artisan rfp:scrape-all --type=open
php artisan rfp:scrape-all --type=past --limit=10 --delay=2
php artisan rfp:scrape-all --institution="University of Illinois" --platform="Bonfire"
```

### 2. Scrape Specific Institution
Target a specific university or college:
```bash
php artisan rfp:scrape-institution "University of Michigan" --platform=Bonfire --type=open
```

### 3. Scrape by Platform
Scrape all associated institutions for a specific bidding platform:
```bash
php artisan rfp:scrape-platform Bonfire --type=open
php artisan rfp:scrape-platform OpenGov --type=past
```

---

## 📝 License

This project is open-sourced under the MIT license.