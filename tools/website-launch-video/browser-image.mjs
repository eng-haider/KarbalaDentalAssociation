// Reuse the image Chromium has already loaded, without a second HTTP request.
export async function readLoadedImage(page, selector) {
  const image = page.locator(selector).first();
  await image.waitFor({ state: 'visible', timeout: 30000 });
  await page.waitForFunction(selector => {
    const image = document.querySelector(selector);
    return image instanceof HTMLImageElement && image.complete && image.naturalWidth > 0;
  }, selector, { timeout: 30000 });

  const { dataUrl, ...metadata } = await image.evaluate(async image => {
    await image.decode();
    const canvas = document.createElement('canvas');
    canvas.width = image.naturalWidth;
    canvas.height = image.naturalHeight;
    const context = canvas.getContext('2d');
    if (!context) throw new Error('Could not export the loaded website image.');
    context.drawImage(image, 0, 0);
    try {
      return {
        dataUrl: canvas.toDataURL('image/png'),
        source: image.currentSrc || image.src,
        width: canvas.width,
        height: canvas.height,
        method: 'Original image pixels exported from the loaded browser image',
      };
    } catch (error) {
      throw new Error(`Could not export the loaded image ${image.currentSrc || image.src}: ${error.message}`);
    }
  });
  if (!dataUrl.startsWith('data:image/png;base64,')) throw new Error('The loaded image did not export as PNG.');
  return { buffer: Buffer.from(dataUrl.slice('data:image/png;base64,'.length), 'base64'), ...metadata };
}
