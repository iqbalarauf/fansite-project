import fs from 'fs';
import * as cheerio from 'cheerio';

async function scrapeJkt48Schedule() {
    try {
        // For demo purposes, read from local HTML file
        // In production, you would fetch from the website
        const html = fs.readFileSync('jkt48.html', 'utf8');
        const $ = cheerio.load(html);

        const results = [];

        // Find all tables with class 'table'
        $('table.table').each((tableIndex, tableElement) => {
            const $table = $(tableElement);
            const tbody = $table.find('tbody');
            if (tbody.length > 0) {
                tbody.find('tr').each((index, row) => {
                    const cells = $(row).find('td');
                    if (cells.length >= 3) {
                        const show = $(cells[0]).text().trim();
                        const setlist = $(cells[1]).text().trim().replace(/\n/g, ' ');
                        const members = $(cells[2]).text().trim();

                        // Check if Cornelia Vanisa is in the members
                        if (members.includes('Cornelia Vanisa')) {
                            results.push({
                                show: show,
                                setlist: setlist
                            });
                        }
                    }
                });
            }
        });

        // console.log('Scraped data:', JSON.stringify(results, null, 2));
        return results;

    } catch (error) {
        console.error('Error scraping:', error);
        return [];
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