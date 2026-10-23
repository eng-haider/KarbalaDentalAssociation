import { bundle } from '@remotion/bundler';
import { getCompositions, openBrowser, renderMedia, renderStill } from '@remotion/renderer';
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { createHash } from 'node:crypto';
import path from 'node:path';
import { ASSETS, OFFERS_ASSETS, OFFERS_OUTPUT, OUTPUT, ROOT, OFFERS_FPS, OFFERS_FRAMES, chromePath } from './company-offers-config.mjs';
import { OFFERS_REVIEW_FRAMES } from './company-offers-timing.mjs';

const manifest = JSON.parse(await readFile(path.join(OFFERS_ASSETS, 'capture-manifest.json'), 'utf8'));
for (const shot of manifest.shots) {
  const file = path.join(ASSETS, shot.file);
  const actual = createHash('sha256').update(await readFile(file)).digest('hex');
  if (actual !== shot.sha256) throw new Error(`Capture changed: ${shot.file}. Run capture-company-offers again.`);
}

await mkdir(path.join(OUTPUT, 'company-offers-video-review'), { recursive: true });
const serveUrl = await bundle({
  entryPoint: path.join(ROOT, 'src/company-offers-index.jsx'),
  publicDir: ASSETS,
  outDir: path.join(ROOT, '.company-offers-bundle'),
});
const browser = await openBrowser('chrome', { browserExecutable: chromePath(), logLevel: 'error' });
try {
  const [composition] = await getCompositions(serveUrl, { puppeteerInstance: browser });
  if (!process.argv.includes('--skip-stills')) {
    for (const frame of OFFERS_REVIEW_FRAMES) {
      await renderStill({
        composition,
        serveUrl,
        puppeteerInstance: browser,
        frame,
        output: path.join(OUTPUT, 'company-offers-video-review', `${String(frame).padStart(3, '0')}.png`),
        imageFormat: 'png',
      });
    }
  }
  if (!process.argv.includes('--stills-only')) {
    let lastProgress = -1;
    await renderMedia({
      composition,
      serveUrl,
      puppeteerInstance: browser,
      outputLocation: OFFERS_OUTPUT,
      codec: 'h264',
      pixelFormat: 'yuv420p',
      crf: 17,
      x264Preset: 'slow',
      colorSpace: 'bt709',
      concurrency: 3,
      imageFormat: 'png',
      muted: true,
      metadata: {
        title: 'عروض الشركات المشاركة — نقابة أطباء الأسنان / فرع كربلاء المقدسة',
        comment: 'A real public website walkthrough. The WhatsApp order was not sent during capture.',
      },
      onProgress: ({ progress }) => {
        const percent = Math.floor(progress * 10) * 10;
        if (percent !== lastProgress) {
          console.log(`Render: ${percent}%`);
          lastProgress = percent;
        }
      },
    });
    await writeFile(path.join(OUTPUT, 'company-offers-video-render.json'), JSON.stringify({
      file: path.basename(OFFERS_OUTPUT),
      source: manifest.source,
      capturedAt: manifest.capturedAt,
      selectedCompany: manifest.shots.find(shot => shot.company)?.company,
      width: 1080,
      height: 1920,
      fps: OFFERS_FPS,
      frames: OFFERS_FRAMES,
      durationSeconds: OFFERS_FRAMES / OFFERS_FPS,
      codec: 'h264',
      pixelFormat: 'yuv420p',
      audio: 'none',
    }, null, 2));
    console.log(`Finished: ${OFFERS_OUTPUT}`);
  }
} finally {
  await browser.close({ silent: true });
}
