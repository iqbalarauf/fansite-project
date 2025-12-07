# JKT48 Scraper

This project includes a web scraper for JKT48 theater schedule that extracts performance information for specific members.

## Features

- Scrapes the JKT48 theater schedule from https://jkt48.com/theater/schedule?lang=id
- Filters performances where "Cornelia Vanisa" is listed as a performing member
- Returns show date/time and setlist name

## Usage

Run the scraper using the Artisan command:

```bash
php artisan app:scrape-jkt48-schedule
```

## Implementation

- Uses Node.js with Cheerio for HTML parsing
- Currently reads from a local HTML file (`jkt48.html`) for demo purposes
- In production, it should be updated to fetch live data from the website

## Dependencies

- Node.js
- Cheerio
- Laravel (for the Artisan command)

## Files

- `scraper.js`: The main scraping script
- `app/Console/Commands/ScrapeJkt48Schedule.php`: Laravel Artisan command
- `jkt48.html`: Sample HTML file for testing