import path from 'node:path';
import { ASSETS, OUTPUT, ROOT, VIEWPORT, chromePath } from './config.mjs';
import { OFFERS_FPS, OFFERS_FRAMES } from './company-offers-timing.mjs';

export { ASSETS, OUTPUT, ROOT, VIEWPORT, chromePath, OFFERS_FPS, OFFERS_FRAMES };

export const DISCOUNTS_URL = 'https://karbaladentalassociation.site/discounts';
export const OFFERS_ASSETS = path.join(ASSETS, 'company-offers');
export const OFFERS_OUTPUT = path.join(OUTPUT, 'karbala-dental-association-company-offers.mp4');
