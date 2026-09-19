import { RenderInternals } from '@remotion/renderer';
import ffmpeg from 'ffmpeg-static';
import { execFileSync } from 'node:child_process';
import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import assert from 'node:assert/strict';
import jsQR from 'jsqr';
import { PNG } from 'pngjs';
import { OUTPUT, PUBLIC_URL, FPS, FRAMES } from './config.mjs';
import { REVIEW_FRAMES, CTA_FRAME } from './timing.mjs';

const file = path.join(OUTPUT, 'karbala-dental-association-launch.mp4');
const probe = await RenderInternals.callFf({
  bin: 'ffprobe', args: ['-v', 'error', '-show_streams', '-show_format', '-of', 'json', file],
  indent: false, logLevel: 'error', options: {}, binariesDirectory: null, cancelSignal: undefined,
});
const metadata = JSON.parse(probe.stdout);
const video = metadata.streams.find(s => s.codec_type === 'video');
assert.equal(video.codec_name, 'h264');
assert.equal(video.width, 1080);
assert.equal(video.height, 1920);
assert.equal(video.pix_fmt, 'yuv420p');
assert.equal(video.r_frame_rate, `${FPS}/1`);
assert.equal(Number(video.nb_frames), FRAMES);
assert(Math.abs(Number(metadata.format.duration) - FRAMES / FPS) < 0.04);
assert.equal(metadata.streams.filter(s => s.codec_type === 'audio').length, 0);

// Decode the complete finished movie, treating decoding errors as failures.
execFileSync(ffmpeg, ['-hide_banner', '-v', 'error', '-xerror', '-i', file, '-f', 'null', '-'], { stdio: 'pipe' });

// Fast-start metadata must precede the media payload for instant web playback.
const bytes = await readFile(file);
const atoms = [];
for (let p = 0; p + 8 <= bytes.length;) {
  let size = bytes.readUInt32BE(p);
  const name = bytes.toString('ascii', p + 4, p + 8);
  if (size === 1) size = Number(bytes.readBigUInt64BE(p + 8));
  atoms.push({ name, offset: p });
  if (size === 0) break;
  assert(size >= 8, 'Invalid MP4 atom');
  p += size;
}
assert(atoms.find(a => a.name === 'moov').offset < atoms.find(a => a.name === 'mdat').offset, 'MP4 needs fast-start metadata');

const select = REVIEW_FRAMES.map(n => `eq(n\\,${n})`).join('+');
execFileSync(ffmpeg, ['-hide_banner', '-v', 'error', '-y', '-i', file, '-vf', `select=${select},scale=324:576,tile=5x2:padding=10:margin=10:color=0x11213a`, '-frames:v', '1', '-update', '1', path.join(OUTPUT, 'launch-video-review', 'encoded-contact-sheet.jpg')]);
execFileSync(ffmpeg, ['-hide_banner', '-v', 'error', '-y', '-ss', String(CTA_FRAME / FPS + 2.2), '-i', file, '-frames:v', '1', '-update', '1', path.join(OUTPUT, 'launch-video-review', 'encoded-cta.png')]);
const cta = PNG.sync.read(await readFile(path.join(OUTPUT, 'launch-video-review', 'encoded-cta.png')));
const qr = jsQR(new Uint8ClampedArray(cta.data), cta.width, cta.height, { inversionAttempts: 'dontInvert' });
assert.equal(qr?.data, PUBLIC_URL, 'The QR must decode correctly from the final encoded video');
const result = {
  passed: true, codec: video.codec_name, width: video.width, height: video.height,
  fps: video.r_frame_rate, frames: Number(video.nb_frames), duration: Number(metadata.format.duration),
  pixelFormat: video.pix_fmt, colorSpace: video.color_space, bytes: bytes.length,
  fullDecode: 'passed', fastStart: true, audioTracks: 0, qrUrl: qr.data,
};
await writeFile(path.join(OUTPUT, 'launch-video-verification.json'), JSON.stringify(result, null, 2));
console.log(JSON.stringify(result, null, 2));
