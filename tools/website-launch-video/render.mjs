import { bundle } from '@remotion/bundler';
import { getCompositions, openBrowser, renderMedia, renderStill } from '@remotion/renderer';
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { createHash } from 'node:crypto';
import path from 'node:path';
import { ASSETS, OUTPUT, ROOT, FRAMES, FPS, chromePath } from './config.mjs';
import { REVIEW_FRAMES } from './timing.mjs';

const manifest = JSON.parse(await readFile(path.join(ASSETS, 'capture-manifest.json'), 'utf8'));
// Stop on missing/changed captures instead of silently substituting placeholders.
for (const shot of manifest.shots) {
  const actual = createHash('sha256').update(await readFile(path.join(ASSETS, shot.file))).digest('hex');
  if (actual !== shot.sha256) throw new Error(`Capture changed: ${shot.file}. Run capture again.`);
}
await mkdir(OUTPUT, { recursive: true });
await mkdir(path.join(OUTPUT, 'launch-video-review'), { recursive: true });
console.log('Bundling the 1080 × 1920 Arabic launch composition.');
const serveUrl = await bundle({ entryPoint: path.join(ROOT, 'src/index.jsx'), publicDir: ASSETS, outDir: path.join(ROOT, '.bundle') });
const browser = await openBrowser('chrome', { browserExecutable: chromePath(), logLevel: 'error' });
try {
  const [composition] = await getCompositions(serveUrl, { puppeteerInstance: browser });
  for (const frame of REVIEW_FRAMES) {
    await renderStill({ composition, serveUrl, puppeteerInstance: browser, frame, output: path.join(OUTPUT, 'launch-video-review', `${String(frame).padStart(3, '0')}.png`), imageFormat: 'png' });
  }
  console.log('Review stills rendered.');
  if (process.argv.includes('--stills-only')) process.exitCode = 0;
  else {
    const outputLocation = path.join(OUTPUT, 'karbala-dental-association-launch.mp4');
    let lastProgress = -1;
    await renderMedia({
      composition, serveUrl, puppeteerInstance: browser, outputLocation,
      codec: 'h264', pixelFormat: 'yuv420p', crf: 17, x264Preset: 'slow',
      colorSpace: 'bt709', concurrency: 3, imageFormat: 'png', muted: true,
      metadata: { title: 'إطلاق الموقع الإلكتروني — نقابة أطباء الأسنان العراقيين / فرع كربلاء المقدسة', comment: 'Real public website captured with Playwright. No generated UI, music or watermark.' },
      onProgress: ({ progress }) => {
        const percent = Math.floor(progress * 10) * 10;
        if (percent !== lastProgress) { console.log(`Render: ${percent}%`); lastProgress = percent; }
      },
    });
    await writeFile(path.join(OUTPUT, 'launch-video-render.json'), JSON.stringify({
      file: path.basename(outputLocation), width: 1080, height: 1920, fps: FPS,
      frames: FRAMES, durationSeconds: FRAMES / FPS, codec: 'h264', pixelFormat: 'yuv420p',
      audio: 'none', source: manifest.source, capturedAt: manifest.capturedAt,
    }, null, 2));
    console.log(`Finished: ${outputLocation}`);
  }
} finally { await browser.close({ silent: true }); }
