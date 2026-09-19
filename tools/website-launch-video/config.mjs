import { existsSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

export const ROOT = fileURLToPath(new URL('./', import.meta.url));
export const ASSETS = path.join(ROOT, 'assets');
export const OUTPUT = path.resolve(ROOT, '../../output');
export const PUBLIC_URL = 'https://karbaladentalassociation.smartclinic.software/';
export const SOURCE_URL = process.env.SITE_URL || PUBLIC_URL;
export { FPS, FRAMES, HERO_FRAMES } from './timing.mjs';
export const VIEWPORT = { width: 600, height: 940 };

export function chromePath() {
  const candidates = [
    process.env.CHROME_PATH,
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    '/usr/bin/google-chrome', '/usr/bin/chromium', '/usr/bin/chromium-browser',
    process.env.PROGRAMFILES && path.join(process.env.PROGRAMFILES, 'Google/Chrome/Application/chrome.exe'),
  ];
  return candidates.find(p => p && existsSync(p));
}

export function ease(t) {
  t = Math.max(0, Math.min(1, t));
  return t * t * (3 - 2 * t);
}
