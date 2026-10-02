/**
 * Client-side miniatures for the v8 upload list (Feature 077).
 *
 * Each queued image is decoded once, centre-cropped onto a small canvas and kept
 * as a blob object URL of a few KB, so hundreds of queued photos never hold their
 * full-size bitmaps. Decodes go through a shared queue so at most
 * MAX_CONCURRENT_DECODES full-size bitmaps exist at any time.
 */

export type UploadPlaceholderIcon = "lucide:image" | "lucide:video" | "lucide:file";

/** 2× the 40 px box, for HiDPI screens. */
const THUMBNAIL_PX = 80;
const MAX_CONCURRENT_DECODES = 2;

type ThumbnailJob = () => Promise<unknown>;

const pending: ThumbnailJob[] = [];
let running = 0;

export function uploadPlaceholderIcon(mime: string): UploadPlaceholderIcon {
	if (mime.startsWith("image/")) {
		return "lucide:image";
	}
	if (mime.startsWith("video/")) {
		return "lucide:video";
	}
	return "lucide:file";
}

/** Only images are decoded; videos and unknown types keep their icon. */
export function shouldDecode(mime: string): boolean {
	return mime.startsWith("image/");
}

/** Centre square of a width × height source. */
export function squareCropRect(width: number, height: number): { sx: number; sy: number; size: number } {
	const size = Math.min(width, height);
	return { sx: Math.floor((width - size) / 2), sy: Math.floor((height - size) / 2), size };
}

function pump(): void {
	while (running < MAX_CONCURRENT_DECODES && pending.length > 0) {
		const job = pending.shift() as ThumbnailJob;
		running++;
		job()
			.catch(() => undefined)
			.finally(() => {
				running--;
				pump();
			});
	}
}

/**
 * Runs the job once a decode slot is free.
 * Returns a cancel function that drops the job if it has not started yet.
 */
export function enqueueThumbnail(job: ThumbnailJob): () => void {
	pending.push(job);
	pump();
	return () => {
		const index = pending.indexOf(job);
		if (index !== -1) {
			pending.splice(index, 1);
		}
	};
}

/** Rejects when the browser cannot decode the file (e.g. HEIC outside Safari). */
export async function createUploadThumbnail(file: File): Promise<string> {
	const bitmap = await createImageBitmap(file, { imageOrientation: "from-image" });
	const canvas = document.createElement("canvas");
	canvas.width = THUMBNAIL_PX;
	canvas.height = THUMBNAIL_PX;
	try {
		const { sx, sy, size } = squareCropRect(bitmap.width, bitmap.height);
		canvas.getContext("2d")?.drawImage(bitmap, sx, sy, size, size, 0, 0, THUMBNAIL_PX, THUMBNAIL_PX);
	} finally {
		bitmap.close();
	}

	// Browsers without WebP encoding fall back to PNG.
	const blob = await new Promise<Blob | null>((resolve) => canvas.toBlob(resolve, "image/webp"));
	if (blob === null) {
		throw new Error("Thumbnail encoding failed");
	}
	return URL.createObjectURL(blob);
}
