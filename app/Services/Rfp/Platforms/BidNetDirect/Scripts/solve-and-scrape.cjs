#!/usr/bin/env node

/**
 * Interactive / Automated browser scraper for BidNet Direct.
 * Automatically locates any installed Chromium-based browser (Chrome, Chromium, Brave, Edge)
 * and opens the target page so human verification can be completed without manual token copying.
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

let puppeteer;
try {
    puppeteer = require('puppeteer-core');
} catch (e) {
    try {
        puppeteer = require('puppeteer');
    } catch (err) {
        console.error(JSON.stringify({
            success: false,
            error: 'Neither puppeteer-core nor puppeteer is installed. Run `npm install --save-dev puppeteer-core`.'
        }));
        process.exit(1);
    }
}

/**
 * Locate any installed Chromium-based browser on the system across platforms.
 */
function findBrowser() {
    const envCandidate = process.env.BIDNET_BROWSER_PATH
        || process.env.PUPPETEER_EXECUTABLE_PATH
        || process.env.CHROME_PATH
        || process.env.BROWSER_PATH;

    if (envCandidate && fs.existsSync(envCandidate)) {
        return envCandidate;
    }

    const platform = process.platform;
    const paths = [];

    if (platform === 'linux') {
        paths.push(
            '/snap/bin/chromium',
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/usr/bin/brave-browser',
            '/usr/bin/microsoft-edge',
            '/usr/bin/microsoft-edge-stable',
            '/var/lib/flatpak/exports/bin/org.chromium.Chromium',
            '/var/lib/flatpak/exports/bin/com.google.Chrome'
        );
    } else if (platform === 'darwin') {
        paths.push(
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            '/Applications/Chromium.app/Contents/MacOS/Chromium',
            '/Applications/Brave Browser.app/Contents/MacOS/Brave Browser',
            '/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge'
        );
    } else if (platform === 'win32') {
        const programFiles = process.env['PROGRAMFILES'] || 'C:\\Program Files';
        const programFilesX86 = process.env['PROGRAMFILES(X86)'] || 'C:\\Program Files (x86)';
        const localAppData = process.env['LOCALAPPDATA'] || '';

        paths.push(
            path.join(programFiles, 'Google\\Chrome\\Application\\chrome.exe'),
            path.join(programFilesX86, 'Google\\Chrome\\Application\\chrome.exe'),
            path.join(localAppData, 'Google\\Chrome\\Application\\chrome.exe'),
            path.join(programFiles, 'Microsoft\\Edge\\Application\\msedge.exe'),
            path.join(programFilesX86, 'Microsoft\\Edge\\Application\\msedge.exe'),
            path.join(programFiles, 'BraveSoftware\\Brave-Browser\\Application\\brave.exe'),
            path.join(programFilesX86, 'BraveSoftware\\Brave-Browser\\Application\\brave.exe')
        );
    }

    for (const candidate of paths) {
        if (candidate && fs.existsSync(candidate)) {
            return candidate;
        }
    }

    // Try finding via PATH commands
    const binaries = platform === 'win32'
        ? ['chrome.exe', 'msedge.exe', 'brave.exe', 'chromium.exe']
        : ['google-chrome', 'google-chrome-stable', 'chromium', 'chromium-browser', 'brave-browser', 'microsoft-edge'];

    for (const bin of binaries) {
        try {
            const cmd = platform === 'win32' ? `where ${bin}` : `which ${bin} 2>/dev/null`;
            const resolved = execSync(cmd, { encoding: 'utf-8' }).split(/\r?\n/)[0]?.trim();
            if (resolved && fs.existsSync(resolved)) {
                return resolved;
            }
        } catch {
            // continue checking
        }
    }

    return null;
}

// Parse command line arguments
const args = process.argv.slice(2);
function getArg(flag, defaultValue = null) {
    const idx = args.indexOf(flag);
    if (idx !== -1 && idx + 1 < args.length) {
        return args[idx + 1];
    }
    return defaultValue;
}

const targetUrl = getArg('--url');
const headlessArg = getArg('--headless', 'false');
const headless = headlessArg === 'true' || headlessArg === '1';
const timeout = parseInt(getArg('--timeout', '180000'), 10);
const outputFile = getArg('--output', null);
const wafTokenArg = getArg('--waf-token', null);
const maxPages = parseInt(getArg('--max-pages', '50'), 10);

if (!targetUrl) {
    console.error(JSON.stringify({
        success: false,
        error: 'Missing required argument: --url <URL>'
    }));
    process.exit(1);
}

const browserPath = findBrowser();
if (!browserPath) {
    console.error(JSON.stringify({
        success: false,
        error: 'No compatible browser found. Please install Chrome, Chromium, Brave, or Edge, or set BIDNET_BROWSER_PATH in .env'
    }));
    process.exit(1);
}

(async () => {
    let browser;
    try {
        browser = await puppeteer.launch({
            executablePath: browserPath,
            headless: headless,
            defaultViewport: null,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-blink-features=AutomationControlled',
                '--window-size=1280,850'
            ]
        });

        const pages = await browser.pages();
        const page = pages.length > 0 ? pages[0] : await browser.newPage();

        await page.setUserAgent(
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
        );

        if (wafTokenArg) {
            try {
                const tokenVal = wafTokenArg.startsWith('aws-waf-token=')
                    ? wafTokenArg.substring('aws-waf-token='.length)
                    : wafTokenArg;
                await page.setCookie({
                    name: 'aws-waf-token',
                    value: tokenVal,
                    domain: '.bidnetdirect.com',
                    path: '/'
                });
                console.error('[BidNet] Pre-attached cached aws-waf-token cookie.');
            } catch (err) {
                console.error('[BidNet] Warning: Failed to pre-set cookie:', err.message);
            }
        }

        console.error(`[BidNet] Opening browser (${browserPath}) to: ${targetUrl}`);
        if (!headless) {
            console.error('[BidNet] If CAPTCHA is shown, please solve it in the browser window.');
        }

        await page.goto(targetUrl, {
            waitUntil: 'domcontentloaded',
            timeout: Math.min(60000, timeout)
        });

        // Check if WAF CAPTCHA or challenge is displayed
        let isCaptcha = await page.evaluate(() => {
            const title = document.title || '';
            const hasCaptchaContainer = document.querySelector('#captcha-container') !== null;
            const hasGoku = typeof window.gokuProps !== 'undefined';
            return title.includes('Human Verification') || hasCaptchaContainer || hasGoku;
        });

        if (isCaptcha) {
            console.error('[BidNet] CAPTCHA detected. Please solve the CAPTCHA in the browser window...');

            // Wait until CAPTCHA is solved and page leaves the Human Verification view
            await page.waitForFunction(() => {
                const title = document.title || '';
                const captchaStillVisible = document.querySelector('#captcha-container') !== null;
                const hasGoku = typeof window.gokuProps !== 'undefined';
                return !title.includes('Human Verification') && !captchaStillVisible && !hasGoku;
            }, {
                timeout: timeout,
                polling: 1000
            });

            console.error('[BidNet] Human verification completed! Waiting for solicitations list to load...');
        }

        // Wait until actual solicitation listings or empty results container are present in the DOM
        try {
            await page.waitForFunction(() => {
                const links = document.querySelectorAll('a.solicitation-link');
                const hasTable = document.querySelector('#solicitationsList') !== null
                    || document.querySelector('table.simpleSolResultsTable') !== null
                    || document.querySelector('tr.mets-table-row') !== null;
                const hasNoResults = document.querySelector('.noResults') !== null
                    || (document.body && document.body.innerText.includes('No solicitations found'));

                return links.length > 0 || (hasTable && document.querySelectorAll('tr').length > 1) || hasNoResults;
            }, {
                timeout: 30000,
                polling: 500
            });
        } catch (e) {
            console.error('[BidNet] Waiting for specific table selector timed out, checking page state...');
        }

        // Wait until the javascript-is-loading class is removed from <html> and body is populated
        try {
            await page.waitForFunction(() => {
                const htmlEl = document.documentElement;
                const stillLoading = htmlEl.classList.contains('javascript-is-loading');
                const bodyReady = document.body && document.body.innerHTML.length > 200;
                return !stillLoading && bodyReady;
            }, {
                timeout: 15000,
                polling: 300
            });
        } catch (e) {
            await new Promise(r => setTimeout(r, 2000));
        }

        // Extra pause to ensure row links are fully rendered
        await new Promise(r => setTimeout(r, 1500));

        const pageContent = await page.content();
        const cookies = await page.cookies();
        const wafCookie = cookies.find(c => c.name === 'aws-waf-token');
        const wafToken = wafCookie ? wafCookie.value : null;
        const userAgent = await page.evaluate(() => navigator.userAgent);

        console.error('[BidNet] Verification complete. Closing browser window immediately.');
        await browser.close();
        browser = null;

        const result = {
            success: true,
            browser: browserPath,
            userAgent: userAgent,
            wafToken: wafToken,
            cookiesCount: cookies.length,
            html: pageContent
        };

        if (outputFile) {
            fs.writeFileSync(outputFile, JSON.stringify(result));
            console.log(JSON.stringify({ success: true, outputFile: outputFile, wafToken: wafToken }));
        } else {
            console.log(JSON.stringify(result));
        }

        process.exit(0);
    } catch (err) {
        if (browser) {
            try {
                await browser.close();
            } catch {}
        }

        console.error(JSON.stringify({
            success: false,
            error: err.message || 'Browser automation encountered an error.'
        }));
        process.exit(1);
    }
})();
