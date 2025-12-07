import puppeteer from 'puppeteer-extra';
import StealthPlugin from 'puppeteer-extra-plugin-stealth';
import * as cheerio from 'cheerio';

puppeteer.use(StealthPlugin());

async function scrapeJkt48Schedule() {
    const browser = await puppeteer.launch({
        headless: true,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--disable-gpu'
        ]
    });

    try {
        const page = await browser.newPage();

        // Set user agent to mimic a real browser
        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

        // Navigate to the page
        await page.goto('https://jkt48.com/theater/schedule?lang=id', {
            waitUntil: 'networkidle2',
            timeout: 60000
        });

        // Wait a bit for dynamic content
        await new Promise(resolve => setTimeout(resolve, 3000));

        // Get the HTML content
        const content = await page.content();
        const $ = cheerio.load(content);

        const results = [];

        // Find all tables with class 'table'
        $('table.table').each((tableIndex, tableElement) => {
            const $table = $(tableElement);
            const tbody = $table.find('tbody');
            if (tbody.length > 0) {
                tbody.find('tr').each((index, row) => {
                    const cells = $(row).find('td');
                    if (cells.length >= 3) {
                        const showText = $(cells[0]).text().trim();
                        const setlist = $(cells[1]).text().trim().replace(/\n/g, ' ');
                        const members = $(cells[2]).text().trim();

                        // Check if Cornelia Vanisa is in the members
                        if (members.includes('Cornelia Vanisa')) {
                            // Extract date from show text
                            const dateMatch = showText.match(/(\d{1,2})\.(\d{1,2})\.(\d{4})/);
                            let formattedDate = null;
                            if (dateMatch) {
                                const day = dateMatch[1].padStart(2, '0');
                                const month = dateMatch[2].padStart(2, '0');
                                const year = dateMatch[3];
                                formattedDate = `${year}-${month}-${day}`;
                            }

                            results.push({
                                date: formattedDate,
                                show: showText,
                                setlist: setlist
                            });
                        }
                    }
                });
            }
        });

        return results;

    } catch (error) {
        console.error('Error scraping:', error);
        return [];
    } finally {
        await browser.close();
    }
}

// Run the scraper
scrapeJkt48Schedule().then(data => {
    console.log(JSON.stringify(data));
    process.exit(0);
}).catch(error => {
    console.error('Error:', error);
    process.exit(1);
});