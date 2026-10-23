// Captures only the public, live ordering journey. The WhatsApp handoff is
// deliberately kept inside the site: it shows the real confirmation state but
// does not open WhatsApp, submit an order, or use personal information.
import { chromium } from 'playwright';
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { createHash } from 'node:crypto';
import path from 'node:path';
import { readLoadedImage } from './browser-image.mjs';
import { OSIF_COMPANIES_URL, OFFERS_ASSETS, VIEWPORT, chromePath } from './company-offers-config.mjs';

await mkdir(OFFERS_ASSETS, { recursive: true });

const browser = await chromium.launch({ executablePath: chromePath(), headless: true });
const manifest = {
  source: OSIF_COMPANIES_URL,
  capturedAt: new Date().toISOString(),
  viewport: VIEWPORT,
  deviceScaleFactor: 2,
  method: 'Playwright screenshots of public pages; no order, payment, WhatsApp message, or personal data submitted.',
  shots: [],
};

try {
  const context = await browser.newContext({
    viewport: VIEWPORT,
    deviceScaleFactor: 2,
    locale: 'ar-IQ',
    permissions: [],
    colorScheme: 'light',
  });
  const page = await context.newPage();

  async function paint() {
    await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
  }

  async function waitForImages(scope = 'body') {
    await page.locator(scope).evaluate(async element => {
      await document.fonts.ready;
      await Promise.all([...element.querySelectorAll('img')].map(async image => {
        image.loading = 'eager';
        if (!image.complete) {
          await new Promise((resolve, reject) => {
            image.addEventListener('load', resolve, { once: true });
            image.addEventListener('error', () => reject(new Error(`Image failed: ${image.currentSrc}`)), { once: true });
          });
        }
        if (image.naturalWidth) await image.decode();
      }));
    });
    await paint();
  }

  async function record(file, details = {}) {
    const target = path.join(OFFERS_ASSETS, file);
    await page.screenshot({ path: target });
    const bytes = await readFile(target);
    manifest.shots.push({ file: `company-offers/${file}`, ...details, sha256: createHash('sha256').update(bytes).digest('hex') });
    console.log(`Captured ${file}`);
  }

  console.log(`Opening ${OSIF_COMPANIES_URL}`);
  const response = await page.goto(OSIF_COMPANIES_URL, { waitUntil: 'domcontentloaded', timeout: 90000 });
  if (!response?.ok()) throw new Error(`OSIF companies page returned HTTP ${response?.status()}`);
  await page.locator('.shop-companies').waitFor({ state: 'visible', timeout: 90000 });
  await page.waitForLoadState('networkidle', { timeout: 45000 });
  await page.addStyleTag({ content: `
    html, body, * { cursor: none !important; }
    ::-webkit-scrollbar { display: none; }
  ` });
  await waitForImages();

  // Capture the actual association mark rendered by the website for the intro.
  const logoSelector = await page.locator('img.brand-emblem').count() ? 'img.brand-emblem' : 'a.navbar-brand img';
  const { buffer: logo, ...logoMetadata } = await readLoadedImage(page, logoSelector);
  await writeFile(path.join(OFFERS_ASSETS, 'logo.png'), logo);
  const logoBytes = await readFile(path.join(OFFERS_ASSETS, 'logo.png'));
  manifest.shots.push({ file: 'company-offers/logo.png', selector: logoSelector, ...logoMetadata, sha256: createHash('sha256').update(logoBytes).digest('hex') });

  await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
  await paint();
  await record('companies.png', { url: page.url(), scrollY: 0 });

  const companies = page.locator('.shop-companies a.shop-company-card[href*="/osif/companies/"]');
  const choices = await companies.evaluateAll(links => links.map(link => ({
    href: link.href,
    text: (link.textContent || '').replace(/\s+/g, ' ').trim(),
  })));
  const selected = choices.find(choice => /\d+\s*منتجات/.test(choice.text) && !choice.text.includes('المنتجات قريباً'));
  if (!selected) throw new Error('No public company with products is available for the guide.');

  await page.locator('.shop-companies').scrollIntoViewIfNeeded();
  await waitForImages('.shop-companies');
  await record('participating-companies.png', { url: page.url(), selectedCompany: selected.text, selectedCompanyUrl: selected.href, scrollY: await page.evaluate(() => window.scrollY) });

  const companyResponse = await page.goto(selected.href, { waitUntil: 'domcontentloaded', timeout: 90000 });
  if (!companyResponse?.ok()) throw new Error(`Company page returned HTTP ${companyResponse?.status()}`);
  await page.locator('.shop-product').first().waitFor({ state: 'visible', timeout: 90000 });
  await page.waitForLoadState('networkidle', { timeout: 45000 });
  await page.addStyleTag({ content: 'html, body, * { cursor: none !important; } ::-webkit-scrollbar { display: none; }' });
  await waitForImages();
  await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
  await paint();
  await record('company-page.png', { url: page.url(), company: selected.text, companyUrl: selected.href, scrollY: 0 });

  const firstProduct = page.locator('.shop-product').first();
  await firstProduct.scrollIntoViewIfNeeded();
  await firstProduct.evaluate(async element => {
    await Promise.all([...element.querySelectorAll('img')].map(async image => {
      image.loading = 'eager';
      if (!image.complete) await new Promise(resolve => image.addEventListener('load', resolve, { once: true }));
      if (image.naturalWidth) await image.decode();
    }));
  });
  await paint();
  await record('product.png', { url: page.url(), company: selected.text, product: await firstProduct.locator('h3').innerText(), scrollY: await page.evaluate(() => window.scrollY) });

  await firstProduct.locator('button.shop-add').click();
  await page.locator('.shop-cartbar').waitFor({ state: 'visible', timeout: 15000 });
  await paint();
  await record('product-added.png', { url: page.url(), company: selected.text, scrollY: await page.evaluate(() => window.scrollY) });

  await page.locator('.shop-cartbar button').click();
  await page.locator('.shop-cart').waitFor({ state: 'visible', timeout: 15000 });
  await paint();
  await record('cart.png', { url: page.url(), company: selected.text, state: 'cart-open' });

  // Preserve the user-visible confirmation after the real button action while
  // preventing its external navigation during this recording only.
  const whatsappButton = page.locator('.shop-cart-foot a.shop-whatsapp');
  await whatsappButton.evaluate(element => element.addEventListener('click', event => event.preventDefault(), { capture: true }));
  await whatsappButton.click();
  await page.locator('.shop-sent').waitFor({ state: 'visible', timeout: 15000 });
  await paint();
  await record('whatsapp-ready.png', { url: page.url(), company: selected.text, state: 'ready-in-whatsapp-no-message-sent' });

  await writeFile(path.join(OFFERS_ASSETS, 'capture-manifest.json'), JSON.stringify(manifest, null, 2));
  console.log(`Complete: ${selected.text}`);
} finally {
  await browser.close();
}
