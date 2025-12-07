# JKT48 Scraper

This project includes a web scraper for JKT48 theater schedule that extracts performance information for specific members.

## Features

- Scrapes the JKT48 theater schedule from https://jkt48.com/theater/schedule?lang=id
- Filters performances where "Cornelia Vanisa" is listed as a performing member
- Returns show date (formatted as yyyy-mm-dd), full show info, and setlist name
- Bypasses Cloudflare protection using Puppeteer with stealth plugin

## Usage

Run the scraper using the Artisan command:

```bash
php artisan app:scrape-jkt48-schedule
```

## Implementation

- Uses Node.js with Puppeteer Extra and Stealth Plugin for bypassing Cloudflare
- Parses HTML with Cheerio
- Extracts and formats dates from show text
- Filters for specific member performances

## Dependencies

- Node.js
- Puppeteer Extra
- Puppeteer Extra Plugin Stealth
- Cheerio
- Laravel (for the Artisan command)

## Files

- `scraper.js`: The main scraping script with Cloudflare bypass
- `app/Console/Commands/ScrapeJkt48Schedule.php`: Laravel Artisan command
- `jkt48.html`: Sample HTML file for testing (fallback)