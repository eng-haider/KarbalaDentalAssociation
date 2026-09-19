// Every website pixel comes from a fresh, signed-out Chromium render.
import { chromium } from 'playwright';
import { mkdir, writeFile, readFile } from 'node:fs/promises';
import { createHash } from 'node:crypto';
import path from 'node:path';
import QRCode from 'qrcode';
import { readLoadedImage } from './browser-image.mjs';
import { ASSETS, SOURCE_URL, PUBLIC_URL, VIEWPORT, HERO_FRAMES, FPS, chromePath, ease } from './config.mjs';
import { DISCOUNT_FRAMES } from './timing.mjs';

await mkdir(path.join(ASSETS, 'hero-frames'), { recursive: true });
await mkdir(path.join(ASSETS, 'discount-frames'), { recursive: true });
await mkdir(path.join(ASSETS, 'fonts'), { recursive: true });
const browser = await chromium.launch({ executablePath: chromePath(), headless: true });
const manifest = {
  source: SOURCE_URL, publicUrl: PUBLIC_URL, capturedAt: new Date().toISOString(),
  browser: browser.version(), viewport: VIEWPORT, deviceScaleFactor: 2,
  method: 'Playwright Chromium screenshots; eased real window scrolling, sampled at 30 fps',
  shots: [],
};
try {
  const context = await browser.newContext({ viewport: VIEWPORT, deviceScaleFactor: 2, locale: 'ar-IQ', permissions: [], colorScheme: 'light' });
  const page = await context.newPage();
  // Do not submit forms or perform member searches; capture only public pages.
  console.log(`Opening ${SOURCE_URL}`);
  const response = await page.goto(SOURCE_URL, { waitUntil: 'domcontentloaded', timeout: 90000 });
  if (!response?.ok()) throw new Error(`Homepage returned HTTP ${response?.status()}`);
  // The public host briefly displays a browser check; network idle alone is insufficient.
  await page.locator('main #hero h1').waitFor({ state: 'visible', timeout: 90000 });
  await page.waitForLoadState('networkidle', { timeout: 45000 });
  await page.evaluate(() => document.fonts.ready);
  await page.waitForFunction(() => document.fonts.check('800 32px Cairo', 'الموقع الإلكتروني'));
  await page.addStyleTag({ content: 'html, body, * { cursor: none !important; } ::-webkit-scrollbar { display: none; }' });

  // Close common consent dialogs only if a real dismiss control is present.
  for (const label of ['رفض الكل', 'قبول الكل', 'Accept all', 'Reject all']) {
    const button = page.getByRole('button', { name: label, exact: true });
    if (await button.count() === 1 && await button.isVisible()) await button.click();
  }
  if (await page.locator('.modal.show, [role="dialog"][aria-modal="true"]').count()) {
    throw new Error('A visible overlay needs to be closed before capture.');
  }

  // Cache the same Google-hosted Cairo files used by the actual site, for offline rendering.
  const cssUrl = await page.locator('link[href*="fonts.googleapis.com/css"]').first().getAttribute('href');
  const cssResponse = await context.request.get(cssUrl);
  if (!cssResponse.ok()) throw new Error('Could not cache the website Cairo font.');
  let css = await cssResponse.text();
  const urls = [...new Set([...css.matchAll(/url\((https:[^)]+)\)/g)].map(m => m[1]))];
  if (!urls.length) throw new Error('No Cairo font files found in the website stylesheet.');
  for (let i = 0; i < urls.length; i++) {
    const response = await context.request.get(urls[i]);
    if (!response.ok()) throw new Error(`Could not cache Cairo font ${i}`);
    const ext = new URL(urls[i]).pathname.split('.').pop();
    const name = `cairo-${i}.${ext}`;
    await writeFile(path.join(ASSETS, 'fonts', name), await response.body());
    css = css.split(urls[i]).join(`./${name}`);
  }
  await writeFile(path.join(ASSETS, 'fonts', 'cairo.css'), css);
  const license = await context.request.get('https://raw.githubusercontent.com/google/fonts/main/ofl/cairo/OFL.txt');
  if (!license.ok()) throw new Error('Could not fetch the Cairo font license.');
  await writeFile(path.join(ASSETS, 'fonts', 'OFL.txt'), await license.text());

  // The host may reject a separate API download even when Chrome loaded the logo.
  // Export its original pixels from the page instead of making another request.
  const { buffer: logo, ...logoMetadata } = await readLoadedImage(page, 'img.brand-emblem');
  await writeFile(path.join(ASSETS, 'logo.png'), logo);
  await provenance('logo.png', { selector: 'img.brand-emblem', ...logoMetadata });
  console.log(`Reused loaded website logo: ${logoMetadata.width} × ${logoMetadata.height}`);
  await QRCode.toFile(path.join(ASSETS, 'qr.png'), PUBLIC_URL, {
    width: 480, margin: 4, errorCorrectionLevel: 'M', color: { dark: '#061634', light: '#ffffff' },
  });

  async function ready(selector) {
    await page.locator(selector).waitFor({ state: 'visible' });
    await page.locator(selector).evaluate(async el => {
      await Promise.all([...el.querySelectorAll('img')].map(async img => {
        img.loading = 'eager';
        if (!img.complete) await new Promise((resolve, reject) => {
          img.addEventListener('load', resolve, { once: true });
          img.addEventListener('error', () => reject(new Error('Website image failed')), { once: true });
        });
        if (!img.naturalWidth) throw new Error('Broken image in captured section');
        await img.decode();
      }));
      await document.fonts.ready;
    });
    await page.waitForTimeout(1400); // Let the site's own reveal transitions finish.
  }

  async function provenance(file, details) {
    const bytes = await readFile(path.join(ASSETS, file));
    manifest.shots.push({ file, ...details, sha256: createHash('sha256').update(bytes).digest('hex') });
  }

  await ready('#hero');
  await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
  await page.screenshot({ path: path.join(ASSETS, 'home.png') });
  await provenance('home.png', { selector: '#hero', scrollY: 0 });
  console.log('Capturing homepage: 132 real browser frames with smooth scrolling.');
  for (let i = 0; i < HERO_FRAMES; i++) {
    const y = 110 * ease((i - 15) / (HERO_FRAMES - 25));
    await page.evaluate(y => window.scrollTo({ top: y, behavior: 'instant' }), y);
    // Commit each eased browser scroll to two real paint cycles before capture.
    await page.evaluate(() => new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r))));
    const file = `hero-frames/${String(i).padStart(4, '0')}.jpg`;
    await page.screenshot({ path: path.join(ASSETS, file), type: 'jpeg', quality: 96 });
    await provenance(file, { scrollY: y, frame: i, time: i / FPS });
    if (i % 30 === 0) console.log(`Homepage: ${i}/${HERO_FRAMES} frames`);
  }

  for (const [id, name] of [['transaction-search', 'search'], ['news', 'news'], ['courses', 'courses']]) {
    const section = page.locator(`#${id}`);
    await section.evaluate(el => window.scrollTo({ top: el.getBoundingClientRect().top + scrollY, behavior: 'instant' }));
    await ready(`#${id}`);
    if (await page.locator('#trxInput').inputValue()) throw new Error('Member search must remain empty.');
    if ((await page.locator('#trxResults').innerText()).trim()) throw new Error('Unexpected member search results.');
    const file = `${name}.png`;
    await page.screenshot({ path: path.join(ASSETS, file) });
    await provenance(file, { selector: `#${id}`, scrollY: await page.evaluate(() => scrollY) });
    console.log(`Captured real section: ${id}`);
  }

  // Give the real dedicated discounts page its own longer, gently scrolling shot.
  const discountLink = await page.locator('a.nav-link--featured').first().getAttribute('href');
  const discountUrl = new URL(discountLink, page.url()).href;
  const discountResponse = await page.goto(discountUrl, { waitUntil: 'domcontentloaded', timeout: 90000 });
  if (!discountResponse?.ok()) throw new Error(`Discounts page returned HTTP ${discountResponse?.status()}`);
  await page.locator('main #discounts').waitFor({ state: 'visible', timeout: 90000 });
  await page.waitForLoadState('networkidle', { timeout: 45000 });
  await page.addStyleTag({ content: 'html, body, * { cursor: none !important; } ::-webkit-scrollbar { display: none; }' });
  await page.locator('#discounts').scrollIntoViewIfNeeded();
  await ready('#discounts');
  await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
  await page.waitForTimeout(800);
  await page.screenshot({ path: path.join(ASSETS, 'discounts-page.png') });
  await provenance('discounts-page.png', { source: discountUrl, scrollY: 0 });
  const discountScroll = await page.locator('#discounts').evaluate(el => {
    const bottom = el.getBoundingClientRect().bottom + scrollY;
    return Math.max(0, Math.min(160, bottom - 740));
  });
  console.log(`Capturing the dedicated discounts page: ${DISCOUNT_FRAMES} frames.`);
  for (let i = 0; i < DISCOUNT_FRAMES; i++) {
    const y = discountScroll * ease((i - 30) / (DISCOUNT_FRAMES - 60));
    await page.evaluate(y => window.scrollTo({ top: y, behavior: 'instant' }), y);
    await page.evaluate(() => new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r))));
    const file = `discount-frames/${String(i).padStart(4, '0')}.jpg`;
    await page.screenshot({ path: path.join(ASSETS, file), type: 'jpeg', quality: 96 });
    await provenance(file, { source: discountUrl, scrollY: y, frame: i, time: i / FPS });
    if (i % 30 === 0) console.log(`Discounts: ${i}/${DISCOUNT_FRAMES} frames`);
  }
  await writeFile(path.join(ASSETS, 'capture-manifest.json'), JSON.stringify(manifest, null, 2));
  console.log('Capture complete. Original site layout, colors and Arabic fonts preserved.');
} finally {
  await browser.close();
}
