import { RenderInternals } from '@remotion/renderer';
import ffmpeg from 'ffmpeg-static';
import { execFileSync } from 'node:child_process';
import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import assert from 'node:assert/strict';
import { OFFERS_OUTPUT, OUTPUT, OFFERS_FPS, OFFERS_FRAMES } from './company-offers-config.mjs';
import { OFFERS_REVIEW_FRAMES } from './company-offers-timing.mjs';

const probe = await RenderInternals.callFf({
  bin: 'ffprobe',
  args: ['-v', 'error', '-show_streams', '-show_format', '-of', 'json', OFFERS_OUTPUT],
  indent: false,
  logLevel: 'error',
  options: {},
  binariesDirectory: null,
  cancelSignal: undefined,
});
const metadata = JSON.parse(probe.stdout);
const video = metadata.streams.find(stream => stream.codec_type === 'video');
assert.equal(video.codec_name, 'h264');
assert.equal(video.width, 1080);
assert.equal(video.height, 1920);
assert.equal(video.pix_fmt, 'yuv420p');
assert.equal(video.r_frame_rate, `${OFFERS_FPS}/1`);
assert.equal(Number(video.nb_frames), OFFERS_FRAMES);
assert(Math.abs(Number(metadata.format.duration) - OFFERS_FRAMES / OFFERS_FPS) < 0.04);
assert.equal(metadata.streams.filter(stream => stream.codec_type === 'audio').length, 0);
execFileSync(ffmpeg, ['-hide_banner', '-v', 'error', '-xerror', '-i', OFFERS_OUTPUT, '-f', 'null', '-'], { stdio: 'pipe' });

const bytes = await readFile(OFFERS_OUTPUT);
const atoms = [];
for (let offset = 0; offset + 8 <= bytes.length;) {
  let size = bytes.readUInt32BE(offset);
  const name = bytes.toString('ascii', offset + 4, offset + 8);
  if (size === 1) size = Number(bytes.readBigUInt64BE(offset + 8));
  atoms.push({ name, offset });
  if (size === 0) break;
  assert(size >= 8, 'Invalid MP4 atom');
  offset += size;
}
assert(atoms.find(atom => atom.name === 'moov').offset < atoms.find(atom => atom.name === 'mdat').offset, 'MP4 needs fast-start metadata');

const select = OFFERS_REVIEW_FRAMES.map(frame => `eq(n\\,${frame})`).join('+');
const review = path.join(OUTPUT, 'company-offers-video-review', 'encoded-contact-sheet.jpg');
execFileSync(ffmpeg, ['-hide_banner', '-v', 'error', '-y', '-i', OFFERS_OUTPUT, '-vf', `select=${select},scale=324:576,tile=4x2:padding=10:margin=10:color=0x11213a`, '-frames:v', '1', '-update', '1', review]);

const result = {
  passed: true,
  codec: video.codec_name,
  width: video.width,
  height: video.height,
  fps: video.r_frame_rate,
  frames: Number(video.nb_frames),
  duration: Number(metadata.format.duration),
  pixelFormat: video.pix_fmt,
  colorSpace: video.color_space,
  bytes: bytes.length,
  fullDecode: 'passed',
  fastStart: true,
  audioTracks: 0,
};
await writeFile(path.join(OUTPUT, 'company-offers-video-verification.json'), JSON.stringify(result, null, 2));
console.log(JSON.stringify(result, null, 2));
