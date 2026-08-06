<script>
/**
 * Automatic Image Compressor & Preview Handler
 * Compresses any selected photo (from 1MB up to 50MB+) down to <= 500 KB
 * before encoding to base64 and setting in the form hidden input.
 */
async function compressAndPreviewPhoto(file, previewId = 'previewPhoto', hiddenInputId = 'photoHiddenInput', targetMaxKB = 490) {
    const previewEl = document.getElementById(previewId);
    const hiddenInput = document.getElementById(hiddenInputId);
    if (!file || !previewEl) return;

    if (!/\.(jpe?g|png|webp|gif|bmp|heic)$/i.test(file.name)) {
        alert('Allowed file types: JPEG, JPG, PNG, WEBP');
        return;
    }

    // Show compressing state in preview container
    previewEl.innerHTML = `
        <div class="d-flex items-center gap-3 p-3 rounded-2xl bg-indigo-50/70 border border-indigo-100 text-indigo-700 animate-pulse mt-2">
            <div class="spinner-border spinner-border-sm text-indigo-600" role="status"></div>
            <div class="text-xs font-medium">
                Compressing image (${(file.size / 1024 / 1024).toFixed(2)} MB) to &le; 500 KB...
            </div>
        </div>
    `;

    try {
        const result = await processImageCompression(file, targetMaxKB);
        
        if (hiddenInput) {
            hiddenInput.value = result.dataUrl;
        }

        const originalMB = (result.originalSizeKB / 1024).toFixed(2);
        const finalKB = result.sizeKB;
        const reductionPct = Math.max(0, Math.round((1 - (finalKB / result.originalSizeKB)) * 100));

        // Render modern preview card
        previewEl.innerHTML = `
            <div class="relative group mt-3 p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
                <div class="relative w-14 h-14 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0">
                    <img src="${result.dataUrl}" alt="Photo Preview" class="w-full h-full object-cover">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                            <i class="bi bi-check-circle-fill me-1 text-[9px]"></i> Ready (&le; 500 KB)
                        </span>
                        ${reductionPct > 0 ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                            <i class="bi bi-lightning-charge-fill me-1 text-[9px]"></i> -${reductionPct}% Size
                        </span>` : ''}
                    </div>
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate mb-0">${file.name}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-0">
                        Original: <span class="line-through">${originalMB} MB</span> &rarr; <span class="font-bold text-emerald-600 dark:text-emerald-400">${finalKB} KB</span>
                    </p>
                </div>
                <button type="button" onclick="removeSelectedPhoto('${previewId}', '${hiddenInputId}')" 
                        class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors" title="Remove Photo">
                    <i class="bi bi-trash3 text-sm"></i>
                </button>
            </div>
        `;
    } catch (err) {
        console.error('Image compression failed:', err);
        previewEl.innerHTML = `
            <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center justify-between mt-2">
                <span>Failed to process image. Please try another photo.</span>
                <button type="button" onclick="previewEl.innerHTML=''" class="text-rose-500 font-bold">&times;</button>
            </div>
        `;
    }
}

function processImageCompression(file, targetMaxKB = 490, maxWidth = 1200, maxHeight = 1200) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                let width = img.width;
                let height = img.height;

                // Scale down dimensions proportionally if image is huge
                if (width > maxWidth || height > maxHeight) {
                    if (width / height > maxWidth / maxHeight) {
                        height = Math.round((height * maxWidth) / width);
                        width = maxWidth;
                    } else {
                        width = Math.round((width * maxHeight) / height);
                        width = maxHeight;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');

                // Fill background for PNG transparency conversion
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, width, height);
                ctx.drawImage(img, 0, 0, width, height);

                let quality = 0.88;
                let dataUrl = canvas.toDataURL('image/jpeg', quality);

                function calcKB(b64) {
                    return (b64.length * 0.75) / 1024;
                }

                let currentKB = calcKB(dataUrl);

                // Iteratively lower quality until size <= targetMaxKB
                while (currentKB > targetMaxKB && quality > 0.15) {
                    quality -= 0.08;
                    dataUrl = canvas.toDataURL('image/jpeg', quality);
                    currentKB = calcKB(dataUrl);
                }

                // If quality reduction wasn't enough, scale down resolution
                if (currentKB > targetMaxKB) {
                    let scale = 0.8;
                    while (currentKB > targetMaxKB && scale > 0.2) {
                        const lowCanvas = document.createElement('canvas');
                        lowCanvas.width = Math.round(width * scale);
                        lowCanvas.height = Math.round(height * scale);
                        const lowCtx = lowCanvas.getContext('2d');
                        lowCtx.fillStyle = '#FFFFFF';
                        lowCtx.fillRect(0, 0, lowCanvas.width, lowCanvas.height);
                        lowCtx.drawImage(canvas, 0, 0, lowCanvas.width, lowCanvas.height);
                        dataUrl = lowCanvas.toDataURL('image/jpeg', 0.75);
                        currentKB = calcKB(dataUrl);
                        scale -= 0.15;
                    }
                }

                resolve({
                    dataUrl: dataUrl,
                    sizeKB: Math.round(currentKB),
                    originalSizeKB: Math.round(file.size / 1024)
                });
            };
            img.onerror = reject;
            img.src = e.target.result;
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

function previewFile(fileInputId = 'formFile', previewId = 'previewPhoto', hiddenInputId = 'photoHiddenInput') {
    const input = document.getElementById(fileInputId) || document.querySelector('input[type=file]');
    if (input && input.files && input.files[0]) {
        compressAndPreviewPhoto(input.files[0], previewId, hiddenInputId);
    }
}

function removeSelectedPhoto(previewId = 'previewPhoto', hiddenInputId = 'photoHiddenInput') {
    const previewEl = document.getElementById(previewId);
    const hiddenInput = document.getElementById(hiddenInputId);
    const fileInput = document.querySelector('input[type=file]');
    
    if (previewEl) previewEl.innerHTML = '';
    if (hiddenInput) hiddenInput.value = '';
    if (fileInput) fileInput.value = '';
}

document.addEventListener('DOMContentLoaded', () => {
    const dropZones = document.querySelectorAll('.photo-dropzone');
    dropZones.forEach(zone => {
        const fileInput = zone.querySelector('input[type=file]') || document.getElementById('formFile');
        if (!fileInput) return;

        ['dragenter', 'dragover'].forEach(eventName => {
            zone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.add('border-indigo-500', 'bg-indigo-50/50');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            zone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.remove('border-indigo-500', 'bg-indigo-50/50');
            }, false);
        });

        zone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                fileInput.files = files;
                compressAndPreviewPhoto(files[0]);
            }
        });
    });
});
</script>