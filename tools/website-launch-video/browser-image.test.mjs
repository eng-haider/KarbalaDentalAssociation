import assert from 'node:assert/strict';
import { createServer } from 'node:http';
import { once } from 'node:events';
import { test } from 'node:test';
import { chromium } from 'playwright';
import { PNG } from 'pngjs';
import { readLoadedImage } from './browser-image.mjs';
import { chromePath } from './config.mjs';

test('exports original logo pixels when the host rejects a separate API download', async () => {
  const source = new PNG({ width: 4, height: 2 });
  for (let i = 0; i < source.data.length; i += 4) {
    source.data.set([12, 45, 107, 255], i);
  }
  const png = PNG.sync.write(source);
  let imageRequests = 0;
  const server = createServer((req, res) => {
    if (req.url === '/logo.png') {
      imageRequests++;
      if (req.headers['sec-fetch-dest'] !== 'image') {
        res.writeHead(403).end('Browser image requests only');
        return;
      }
      res.writeHead(200, { 'Content-Type': 'image/png' }).end(png);
      return;
    }
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' }).end('<img class="brand-emblem" src="/logo.png" style="width:80px;height:40px">');
  });
  server.listen(0, '127.0.0.1');
  await once(server, 'listening');
  let browser;
  try {
    browser = await chromium.launch({ executablePath: chromePath(), headless: true });
    const context = await browser.newContext({ deviceScaleFactor: 2 });
    const page = await context.newPage();
    const url = `http://127.0.0.1:${server.address().port}`;
    await page.goto(url, { waitUntil: 'networkidle' });
    const rejected = await context.request.get(`${url}/logo.png`);
    assert.equal(rejected.status(), 403, 'Reproduce the failed API-download approach');
    const requestsBeforeExport = imageRequests;
    const image = await readLoadedImage(page, 'img.brand-emblem');
    const decoded = PNG.sync.read(image.buffer);
    assert.equal(imageRequests, requestsBeforeExport, 'Export must not download the logo again');
    assert.equal(image.source, `${url}/logo.png`);
    assert.equal(decoded.width, 4, 'Preserve intrinsic resolution, not the CSS display size');
    assert.equal(decoded.height, 2);
    assert.deepEqual(decoded.data, source.data, 'Preserve the original logo pixels');
  } finally {
    await browser?.close();
    server.closeAllConnections();
    await new Promise(resolve => server.close(resolve));
  }
});
