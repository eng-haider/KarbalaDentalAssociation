{{--
    Converts HEIC photos to JPEG in the browser, before they are uploaded.

    An iPhone shoots HEIC, and only Apple devices can decode it — which is
    exactly the device the photo is being uploaded from, so the conversion is
    done where it is guaranteed to work instead of relying on the server having
    a HEIC decoder. Anything this cannot convert is uploaded untouched and the
    server tries again (\App\Support\HeicConverter).
--}}
<script>
    (() => {
        const isHeic = (file) =>
            /\.hei[cf]$/i.test(file.name) || /^image\/hei[cf]/i.test(file.type);

        const toJpeg = async (file) => {
            try {
                const bitmap = await createImageBitmap(file);
                const canvas = document.createElement('canvas');
                canvas.width = bitmap.width;
                canvas.height = bitmap.height;
                canvas.getContext('2d').drawImage(bitmap, 0, 0);
                bitmap.close?.();

                const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.9));

                if (! blob) {
                    return file;
                }

                return new File([blob], file.name.replace(/\.hei[cf]$/i, '.jpg'), {
                    type: 'image/jpeg',
                    lastModified: file.lastModified,
                });
            } catch (error) {
                return file; // Browser cannot decode it; let the server try.
            }
        };

        // Capture phase: FilePond reads `input.files` in its own listener, so the
        // files have to be swapped before the event reaches it.
        document.addEventListener('change', async (event) => {
            const input = event.target;

            if (! (input instanceof HTMLInputElement) || input.type !== 'file') {
                return;
            }

            if (input.dataset.heicConverted === '1') {
                return;
            }

            const files = Array.from(input.files ?? []);

            if (! files.some(isHeic)) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            const converted = [];

            for (const file of files) {
                converted.push(isHeic(file) ? await toJpeg(file) : file);
            }

            const transfer = new DataTransfer();
            converted.forEach((file) => transfer.items.add(file));

            input.dataset.heicConverted = '1';
            input.files = transfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
            delete input.dataset.heicConverted;
        }, true);
    })();
</script>
