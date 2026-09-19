# Website launch Reel

28 seconds · 1080 × 1920 · 30 fps · H.264 / yuv420p · silent · no watermark.

All website imagery is captured from the **real public homepage and discounts page in Chromium**.
The production frontend is not modified. Cairo, the logo, navy and gold come from
the website itself. No generated UI, avatars, stock mockups or music are used.

## Generate

Install Node.js 20+ and Google Chrome, then install this isolated toolchain once:

```sh
npm ci --prefix tools/website-launch-video
```

From the repository root:

```sh
npm run launch-video
```

The final file is:

```text
output/karbala-dental-association-launch.mp4
```

The capture requires internet access to the public site, Google Fonts and the
Cairo license. It launches a fresh signed-out browser with no notification
permissions. It waits for the actual homepage, network idle, fonts, image
decoding and reveal animations. It leaves transaction search empty and captures
only the public hero, search, activities, courses and the dedicated discounts
page. Member listings are not included in the discounts shot.
The logo is exported at its intrinsic resolution from the image already loaded
by Chromium, so a separate logo-download request is unnecessary. Its source URL,
dimensions and hash are recorded in the capture manifest.

If the live host is unavailable, start the existing Laravel application with its
normal local configuration, then use the same real local frontend:

```sh
SITE_URL=http://127.0.0.1:8000 npm run launch-video
```

Do not seed invented content for the recording. Review local public content
before sharing a capture. The final CTA always points to the official live URL.

`CHROME_PATH=/absolute/path/to/chrome` overrides browser discovery. On Linux,
install Chrome/Chromium, or run `npx playwright install chromium` inside the
`tools/website-launch-video` directory if no system browser is available.

## Edit and render again

The composition lives in `src/LaunchVideo.jsx`; shared timing is in `timing.mjs`.
After a successful capture, rendering uses the locally cached assets:

```sh
npm run render-launch-video
```

For review stills without encoding the movie:

```sh
cd tools/website-launch-video
node render.mjs --stills-only
```

Review PNGs are in `output/launch-video-review/`. The capture manifest records
source URL, browser, viewport, capture time, scroll position and SHA-256 hashes
for every screenshot. Rendering checks those hashes before building the movie.
Captured images, fonts, build products and final outputs are ignored by Git.
Dependencies are pinned in this directory's own lockfile.

Both generation commands verify dimensions, frame count, frame rate, duration,
H.264 encoding, compatible pixel format, silent audio state and MP4 fast-start.
They decode the whole finished movie, extract a contact sheet and confirm the
QR URL from the encoded MP4. Results are saved to
`output/launch-video-verification.json`.

## Timeline

| Time | Content |
| --- | --- |
| 0–3 s | Real association emblem; Arabic launch title |
| 3–7 s | Homepage reveal and eased browser scrolling |
| 7–10 s | Electronic services with a large Arabic title |
| 10–13 s | Association activities |
| 13–16 s | Training courses |
| 16–21 s | Dedicated discounts page with gentle browser scrolling |
| 21–24 s | Homepage with “خدمات النقابة… الآن أقرب إليكم” |
| 24–28 s | Emblem, Arabic CTA, full official URL and QR code |

Section headings use bold 78 px Cairo text. Page transitions overlap by 18
frames (600 ms). There is no audio track, allowing licensed
music to be added in Instagram. Cairo's OFL license is cached alongside its font
files. The final MP4 is encoded with Remotion's bundled FFmpeg at CRF 17, with
BT.709 color and 4:2:0 pixels for Instagram compatibility.
