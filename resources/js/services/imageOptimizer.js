/**
 * Image Optimizer Service
 * Handles client-side photo resizing, format validation, and cover-crop preview generation.
 * Prevents massive multi-megabyte photo uploads while maintaining high quality.
 */

export const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
export const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024; // 10MB limit

/**
 * Validates whether the given file is an allowed image type and within size limits.
 */
export const validateImageFile = (file) => {
    if (!file) return { valid: false, error: 'Tidak ada file yang dipilih.' };

    const type = file.type?.toLowerCase();
    if (!ALLOWED_MIME_TYPES.includes(type)) {
        return {
            valid: false,
            error: 'Format foto tidak didukung. Harap gunakan format JPG, PNG, atau WEBP.',
        };
    }

    if (file.size > MAX_FILE_SIZE_BYTES) {
        return {
            valid: false,
            error: 'Ukuran foto terlalu besar (maksimal 10 MB).',
        };
    }

    return { valid: true };
};

/**
 * Optimizes an image: resizes to max dimensions (1920px), compresses to high-quality JPEG.
 * Returns { file: File, dataUrl: string, width: number, height: number }
 */
export const optimizeImage = (fileOrBlob, fileName = 'photo.jpg', maxWidth = 1920, maxHeight = 1080, quality = 0.85) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();

        reader.onload = (e) => {
            const img = new Image();

            img.onload = () => {
                let { width, height } = img;

                // Calculate proportional scaling
                if (width > maxWidth || height > maxHeight) {
                    const ratio = Math.min(maxWidth / width, maxHeight / height);
                    width = Math.round(width * ratio);
                    height = Math.round(height * ratio);
                }

                // Render onto canvas
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';
                ctx.drawImage(img, 0, 0, width, height);

                // Export as JPEG blob and data URL
                canvas.toBlob(
                    (blob) => {
                        if (!blob) {
                            reject(new Error('Gagal mengompresi gambar.'));
                            return;
                        }

                        const cleanFileName = fileName.replace(/\.[^/.]+$/, '') + '.jpg';
                        const optimizedFile = new File([blob], cleanFileName, {
                            type: 'image/jpeg',
                            lastModified: Date.now(),
                        });

                        const dataUrl = canvas.toDataURL('image/jpeg', quality);

                        resolve({
                            file: optimizedFile,
                            dataUrl,
                            width,
                            height,
                            size: blob.size,
                        });
                    },
                    'image/jpeg',
                    quality
                );
            };

            img.onerror = () => {
                reject(new Error('Gagal memuat gambar untuk optimasi.'));
            };

            img.src = e.target.result;
        };

        reader.onerror = () => {
            reject(new Error('Gagal membaca file foto.'));
        };

        reader.readAsDataURL(fileOrBlob);
    });
};

/**
 * Convert Base64/DataURL to File
 */
export const dataUrlToFile = (dataUrl, filename = 'photo.jpg') => {
    const arr = dataUrl.split(',');
    const mime = arr[0].match(/:(.*?);/)[1] || 'image/jpeg';
    const bstr = atob(arr[1]);
    let n = bstr.length;
    const u8arr = new Uint8Array(n);
    while (n--) {
        u8arr[n] = bstr.charCodeAt(n);
    }
    return new File([u8arr], filename, { type: mime });
};

