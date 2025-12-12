# JKT48 Scraper

This project includes a web scraper for JKT48 theater schedule that extracts performance information for specific members.

## Features

- Scrapes the JKT48 theater schedule from https://jkt48.com/theater/schedule?lang=id
- Filters performances where "Cornelia Vanisa" is listed as a performing member
- Returns show date (formatted as yyyy-mm-dd), full show info, and setlist name
- Bypasses Cloudflare protection using Puppeteer with stealth plugin
- Saves scraped data to the `show_teater` database table with sequential `show_id`

## Usage

### Manual Run

Run the scraper using the Artisan command:

```bash
php artisan app:scrape-jkt48-schedule
```

### Automated Schedule (Cron Job)

The scraper is scheduled to run automatically **every Tuesday at 9:00 AM (Asia/Jakarta timezone)**.

To enable the scheduler, add this cron entry to your server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**For Windows (Task Scheduler):**
1. Open Task Scheduler
2. Create a new Basic Task
3. Set trigger to run daily
4. Set action to run program: `php.exe`
5. Add arguments: `artisan schedule:run`
6. Set start in: `C:\path\to\your\project`

**For Linux/macOS:**
```bash
# Edit crontab
crontab -e

# Add this line
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**For Hostinger Shared Hosting:**

1. Login ke hPanel Hostinger (https://hpanel.hostinger.com)
2. Buka menu **Advanced** → **Cron Jobs**
3. Klik **Create New Cron Job**
4. Setup dengan konfigurasi:
   - **Schedule**: Every Minute (`* * * * *`)
   - **Command**:
     ```bash
     cd /home/uXXXXXXXXX/domains/yourdomain.com/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
     ```
5. Ganti:
   - `uXXXXXXXXX` dengan username hosting Anda
   - `yourdomain.com` dengan domain Anda
   - `public_html` dengan folder root project (cek di File Manager)
6. Klik **Create**

📖 **Lihat [SCHEDULER_SETUP.md](SCHEDULER_SETUP.md) untuk panduan lengkap setup Hostinger**

The schedule configuration is defined in `routes/console.php`.

### Test the Schedule

You can test the scheduled task without waiting:

```bash
php artisan schedule:list
php artisan schedule:test
```

## Database Integration

The scraper saves data to the `show_teater` table:
- `show_id`: Sequential ID based on date order (1, 2, 3, etc.)
- `show_date`: Show date in yyyy-mm-dd format
- `setlist`: Setlist name

## Implementation

- Uses Node.js with Puppeteer Extra and Stealth Plugin for bypassing Cloudflare
- Parses HTML with Cheerio
- Extracts and formats dates from show text
- Filters for specific member performances
- Laravel model `ShowTeater` for database operations

## Dependencies

- Node.js
- Puppeteer Extra
- Puppeteer Extra Plugin Stealth
- Cheerio
- Laravel (for the Artisan command and database model)

## Files

- `scraper.js`: The main scraping script with Cloudflare bypass
- `app/Console/Commands/ScrapeJkt48Schedule.php`: Laravel Artisan command
- `app/Models/ShowTeater.php`: Eloquent model for show_teater table
- `jkt48.html`: Sample HTML file for testing (fallback)
