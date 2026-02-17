/**
 * Type definitions for Lychee Embed Widget
 */

/**
 * Layout types supported by the widget
 */
export type LayoutType = "square" | "masonry" | "grid" | "justified" | "filmstrip";

/**
 * Header placement options
 */
export type HeaderPlacement = "top" | "bottom" | "none";

/**
 * Size variant types available for photos
 */
export type SizeVariantType = "placeholder" | "thumb" | "thumb2x" | "small" | "small2x" | "medium" | "medium2x" | "original";

/**
 * Embed mode types
 */
export type EmbedMode = "album" | "stream";

/**
 * Widget configuration options
 */
export interface EmbedConfig {
	/** The Lychee API base URL */
	apiUrl: string;
	/** Embed mode: 'album' for specific album, 'stream' for all public photos */
	mode?: EmbedMode;
	/** Album ID to display (required for album mode) */
	albumId?: string;
	/** Layout type */
	layout: LayoutType;
	/** Widget width (px or %) */
	width?: string;
	/** Widget height (px or %) */
	height?: string;
	/** Gap between photos in pixels */
	spacing?: number;
	/** Target row height for justified layout (px) */
	targetRowHeight?: number;
	/** Target column width for grid/masonry layouts (px) */
	targetColumnWidth?: number;
	/** Maximum number of photos to display, or 'none' for all photos */
	maxPhotos?: number | "none";
	/** Sort order: 'desc' (newest first, default) or 'asc' (oldest first) */
	sortOrder?: "asc" | "desc";
	/** Whether to show album title */
	showTitle?: boolean;
	/** Whether to show album description */
	showDescription?: boolean;
	/** Whether to show photo captions */
	showCaptions?: boolean;
	/** Whether to show EXIF data in lightbox */
	showExif?: boolean;
	/** Header placement: 'top' (full header), 'bottom' (simple link), or 'none' */
	headerPlacement?: HeaderPlacement;
	/** Filter photos by uploader username */
	author?: string;
	/** Custom CSS class for widget container */
	containerClass?: string;
}

/**
 * Size variant data from API
 */
export interface SizeVariantData {
	type: string;
	locale: string;
	filesize: string;
	height: number;
	width: number;
	url: string | null;
	is_watermarked: boolean;
}

/**
 * Photo EXIF data
 */
export interface PhotoExif {
	make: string | null;
	model: string | null;
	lens: string | null;
	iso: string | null;
	aperture: string | null;
	shutter: string | null;
	focal: string | null;
	taken_at: string | null;
}

/**
 * Photo data from API
 */
export interface Photo {
	id: string;
	title: string | null;
	description: string | null;
	is_video: boolean;
	duration: string | null;
	size_variants: {
		placeholder: SizeVariantData | null;
		thumb: SizeVariantData | null;
		thumb2x: SizeVariantData | null;
		small: SizeVariantData | null;
		small2x: SizeVariantData | null;
		medium: SizeVariantData | null;
		medium2x: SizeVariantData | null;
		original: SizeVariantData | null;
	};
	exif: PhotoExif;
}

/**
 * Album data from API
 */
export interface Album {
	id: string;
	title: string;
	description: string | null;
	photo_count: number;
	copyright: string | null;
	license: string | null;
}

/**
 * API response for album embed endpoint
 */
export interface EmbedApiResponse {
	album: Album;
	photos: Photo[];
}

/**
 * API response for stream embed endpoint
 */
export interface EmbedStreamApiResponse {
	site_title: string;
	photos: Photo[];
}

/**
 * Photo with calculated position for layout
 */
export interface PositionedPhoto extends Photo {
	position: {
		top: number;
		left: number;
		width: number;
		height: number;
	};
}

/**
 * Layout calculation result
 */
export interface LayoutResult {
	photos: PositionedPhoto[];
	containerHeight: number;
}

/**
 * Column for grid/masonry layouts
 */
export interface Column {
	photos: Photo[];
	height: number;
}

/**
 * Responsive breakpoint configuration
 */
export interface Breakpoint {
	minWidth: number;
	columns: number;
}
