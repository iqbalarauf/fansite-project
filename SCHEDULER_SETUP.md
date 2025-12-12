# Laravel Task Scheduler Setup

This guide explains how to set up the Laravel task scheduler to run automated tasks, including the JKT48 scraper.

## Scheduled Tasks

### JKT48 Theater Schedule Scraper
- **Command**: `app:scrape-jkt48-schedule`
- **Schedule**: Every Tuesday at 9:00 AM (Asia/Jakarta)
- **Purpose**: Automatically scrape JKT48 schedule and save performances to database

## Setup Instructions

### 1. View Scheduled Tasks

To see all scheduled tasks:

```bash
php artisan schedule:list
```

### 2. Test Schedule Manually

To test the scheduler without waiting for the scheduled time:

```bash
php artisan schedule:run
```

### 3. Production Setup

#### Linux/Ubuntu/Debian Server

1. Open crontab editor:
```bash
crontab -e
```

2. Add this line (replace `/path/to/project` with your actual project path):
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

3. Save and exit. The scheduler will now run every minute and execute any due tasks.

#### Windows Server

**Option 1: Task Scheduler (Recommended)**

1. Open **Task Scheduler**
2. Click **Create Basic Task**
3. Name: `Laravel Scheduler`
4. Trigger: **Daily** at 12:00 AM
5. Action: **Start a program**
   - Program/script: `C:\path\to\php.exe`
   - Arguments: `artisan schedule:run`
   - Start in: `C:\path\to\project`
6. Open task properties and configure:
   - Under **Triggers**, edit trigger and check "Repeat task every: **1 minute**" for a duration of "**1 day**"
   - Under **Settings**, uncheck "Stop the task if it runs longer than"

**Option 2: PowerShell Script**

Create a PowerShell script `run-scheduler.ps1`:

```powershell
Set-Location "C:\path\to\project"
php artisan schedule:run
```

Then create a scheduled task to run this script every minute.

#### Hostinger Shared Hosting

Hostinger menyediakan Cron Jobs melalui hPanel. Berikut cara setupnya:

1. **Login ke hPanel Hostinger**
   - Buka https://hpanel.hostinger.com
   - Login dengan akun Hostinger Anda

2. **Buka Cron Jobs**
   - Di dashboard, cari menu **Advanced** atau **Lanjutan**
   - Klik **Cron Jobs**

3. **Tambah Cron Job Baru**
   - Klik tombol **Create New Cron Job** atau **Buat Cron Job Baru**

4. **Konfigurasi Cron Job**
   
   **Common Settings (Pengaturan Umum):**
   - Pilih **Every Minute** atau **Custom**
   
   **Custom Schedule (jika dipilih):**
   - Minute: `*`
   - Hour: `*`
   - Day: `*`
   - Month: `*`
   - Weekday: `*`
   
   **Command to Execute:**
   ```bash
   cd /home/u123456789/domains/yourdomain.com/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
   ```
   
   **Ganti path sesuai dengan struktur Hostinger Anda:**
   - `/home/u123456789` → Username hosting Anda (cek di File Manager)
   - `yourdomain.com` → Domain Anda
   - `public_html` → Folder root website (atau `htdocs` tergantung setup)

5. **Cara Menemukan Path yang Benar**
   
   Via **File Manager** di hPanel:
   - Buka File Manager
   - Path lengkap akan terlihat di address bar
   - Contoh: `/home/u123456789/domains/yourdomain.com/public_html`
   
   Via **SSH** (jika tersedia):
   ```bash
   pwd
   # Output: /home/u123456789/domains/yourdomain.com/public_html
   ```

6. **Menemukan Path PHP yang Benar**
   
   Via SSH:
   ```bash
   which php
   # atau
   which php8.1
   ```
   
   Path PHP Hostinger yang umum:
   - `/usr/bin/php` (PHP versi default)
   - `/usr/bin/php8.1` (PHP 8.1)
   - `/usr/bin/php8.2` (PHP 8.2)
   - `/opt/alt/php81/usr/bin/php` (alternative path)

7. **Contoh Command Lengkap untuk Hostinger**
   
   ```bash
   cd /home/u123456789/domains/example.com/public_html && /usr/bin/php8.2 artisan schedule:run >> /dev/null 2>&1
   ```
   
   Atau dengan email notifikasi error:
   ```bash
   cd /home/u123456789/domains/example.com/public_html && /usr/bin/php8.2 artisan schedule:run
   ```

8. **Aktifkan Cron Job**
   - Klik **Create** atau **Simpan**
   - Status harus **Active** (hijau)

9. **Tips untuk Hostinger**
   
   - **Gunakan absolute path** untuk PHP dan project directory
   - **Pilih PHP version** yang sesuai dengan requirements Laravel Anda
   - **Check logs** di `storage/logs/laravel.log` untuk memastikan berjalan
   - **Email notifications**: Hostinger akan email jika ada error (optional)
   - **Limit**: Gratis/shared hosting biasanya limit 1-2 cron jobs

10. **Troubleshooting Hostinger**
    
    **Cron tidak berjalan:**
    ```bash
    # Test manual via SSH
    cd /home/u123456789/domains/example.com/public_html
    php artisan schedule:run -v
    ```
    
    **Permission denied:**
    ```bash
    # Set permissions via SSH atau File Manager
    chmod 755 artisan
    chmod -R 775 storage
    chmod -R 775 bootstrap/cache
    ```
    
    **PHP version mismatch:**
    - Pastikan PHP version di cron sama dengan web
    - Check di hPanel > PHP Configuration
    - Gunakan absolute path ke PHP version tertentu

#### Using Laravel Forge or Envoyer

These services automatically configure the scheduler for you. No additional setup needed.

### 4. Verify Setup

After setting up the cron job, you can verify it's working:

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Or manually trigger the scheduler
php artisan schedule:run -v
```

## Troubleshooting

### Scheduler Not Running

1. **Check PHP Path**: Make sure `php` command points to the correct PHP version:
   ```bash
   which php
   php -v
   ```

2. **Check Permissions**: Ensure the web server user has execute permissions:
   ```bash
   chmod +x artisan
   ```

3. **Check Logs**: Look for errors in Laravel logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Verify Cron is Running**:
   ```bash
   # Linux
   sudo service cron status
   
   # macOS
   sudo launchctl list | grep cron
   ```

### Task Not Executing at Scheduled Time

1. Check timezone configuration in `config/app.php`:
   ```php
   'timezone' => 'Asia/Jakarta',
   ```

2. Verify schedule definition in `routes/console.php`

3. Test manually:
   ```bash
   php artisan app:scrape-jkt48-schedule
   ```

## Additional Resources

- [Laravel Task Scheduling Documentation](https://laravel.com/docs/scheduling)
- [Cron Expression Generator](https://crontab.guru/)
- [Laravel Scheduler Cheat Sheet](https://laravel.com/docs/scheduling#schedule-frequency-options)

## Schedule Frequency Options

You can modify the schedule in `routes/console.php`:

```php
// Run every minute
Schedule::command('command')->everyMinute();

// Run hourly
Schedule::command('command')->hourly();

// Run daily at specific time
Schedule::command('command')->dailyAt('13:00');

// Run weekly on specific day
Schedule::command('command')->weekly()->tuesdays()->at('09:00');

// Run monthly
Schedule::command('command')->monthly();

// Run on weekdays only
Schedule::command('command')->weekdays();

// Run with custom cron expression
Schedule::command('command')->cron('0 9 * * 2');
```
