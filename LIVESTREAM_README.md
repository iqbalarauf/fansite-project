# JKT48 Scraper & Live Stream Checker

This project includes tools for JKT48 theater schedule scraping and live stream status checking.

## Features

### Theater Schedule Scraper
- Scrapes the JKT48 theater schedule from https://jkt48.com/theater/schedule?lang=id
- Filters performances where "Cornelia Vanisa" is listed as a performing member
- Returns show date (formatted as yyyy-mm-dd), full show info, and setlist name
- Bypasses Cloudflare protection using Puppeteer with stealth plugin
- Saves scraped data to the `show_teater` database table with sequential `show_id`

### Live Stream Status Checker
- Checks if JKT48 members are currently live streaming on IDN Live platform
- Supports single username check and multiple username check
- Returns live status, message, and timestamp
- Handles API failures gracefully

## Usage

### Theater Schedule Scraper
Run the scraper using the Artisan command:

```bash
php artisan app:scrape-jkt48-schedule
```

### Live Stream Status Checker

#### Check Single Username
```http
GET /api/livestream/{username}
```

Example:
```http
GET /api/livestream/jkt48_cynthia
```

Response:
```json
{
  "username": "jkt48_cynthia",
  "is_live": false,
  "message": "User not live",
  "timestamp": "2025-12-07T05:00:00.000000Z",
  "source": "api.idn.app/v2"
}
```

#### Check Multiple Usernames
```http
POST /api/livestream/check-multiple
Content-Type: application/json

{
  "usernames": ["jkt48_cynthia", "jkt48_fiony", "jkt48_adel"]
}
```

Response:
```json
{
  "results": [
    {
      "username": "jkt48_cynthia",
      "is_live": false,
      "checked_at": "2025-12-07T05:00:00.000000Z"
    }
  ],
  "total_checked": 3,
  "live_count": 0,
  "timestamp": "2025-12-07T05:00:00.000000Z"
}
```

## Database Integration

The scraper saves data to the `show_teater` table:
- `show_id`: Sequential ID based on date order (continues from existing data)
- `show_date`: Show date in yyyy-mm-dd format
- `setlist`: Setlist name

## Implementation

### Theater Scraper
- Uses Node.js with Puppeteer Extra and Stealth Plugin for bypassing Cloudflare
- Parses HTML with Cheerio
- Extracts and formats dates from show text
- Filters for specific member performances
- Laravel model `ShowTeater` for database operations

### Live Stream Checker
- Uses Laravel HTTP client for API calls
- Tries multiple IDN Live API endpoints for reliability
- Returns structured JSON responses
- Handles timeouts and errors gracefully

## Dependencies

- Node.js
- Puppeteer Extra
- Puppeteer Extra Plugin Stealth
- Cheerio
- Laravel (for the Artisan command, database model, and API routes)

## Files

- `scraper.js`: The main scraping script with Cloudflare bypass
- `app/Console/Commands/ScrapeJkt48Schedule.php`: Laravel Artisan command
- `app/Models/ShowTeater.php`: Eloquent model for show_teater table
- `app/Http/Controllers/LiveStreamController.php`: Controller for live stream checks
- `routes/api.php`: API routes for live stream endpoints
- `jkt48.html`: Sample HTML file for testing (fallback)</content>
<parameter name="filePath">c:\Users\hp\Documents\IT Project FILES\Eksperimen\fansite-project\LIVESTREAM_README.md