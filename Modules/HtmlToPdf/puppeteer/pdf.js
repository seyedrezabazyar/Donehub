#!/usr/bin/env node

import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';

// گرفتن آرگومان‌های خط فرمان
const args = process.argv.slice(2);
if (args.length < 2) {
    console.error('Usage: node pdf.js <input.html> <output.pdf>');
    process.exit(1);
}

const [htmlPath, pdfPath] = args;

// بررسی وجود فایل HTML
if (!fs.existsSync(htmlPath)) {
    console.error('Input HTML file does not exist:', htmlPath);
    process.exit(1);
}

(async () => {
    try {
        // راه‌اندازی مرورگر
        const browser = await puppeteer.launch({
            executablePath: '/usr/bin/google-chrome', // مسیر Chrome
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });

        const page = await browser.newPage();

        // بارگذاری فایل HTML محلی
        const htmlContent = fs.readFileSync(htmlPath, 'utf8');
        await page.setContent(htmlContent, { waitUntil: 'networkidle0' });

        // تبدیل به PDF
        await page.pdf({
            path: pdfPath,
            format: 'A4',
            printBackground: true
        });

        await browser.close();
        console.log('PDF generated successfully:', pdfPath);
        process.exit(0);

    } catch (err) {
        console.error('Error generating PDF:', err);
        process.exit(1);
    }
})();
