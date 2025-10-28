/**
 * Type definitions for Lychee Embed Widget
 */

/**
 * Layout types supported by the widget
 */
export type LayoutType = "square" | "masonry" | "grid" | "justified" | "filmstrip";

/**
 * Size variant types available for photos
 */
export type SizeVariantType = "placeholder" | "thumb" | "thumb2x" | "small" | "small2x" | "medium" | "medium2x" | "original";

/**
 * Widget configuration options
 */
export interface EmbedConfig {
	/** The Lychee API base URL */
	apiUrl: string;
	/** Album ID to display */
	albumId: string;
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
	/** Whether to show album title */
	showTitle?: boolean;
	/** Whether to show album description */
	showDescription?: boolean;
	/** Whether to show photo captions */
	showCaptions?: boolean;
	/** Whether to show EXIF data in lightbox */
	showExif?: boolean;
	/** Theme: 'light' or 'dark' */
	theme?: "light" | "dark";
	/** Custom CSS class for widget container */
	containerClass?: string;
}

/**
 * Size variant data from API
 */
export interface SizeVariantData {
	url: string;
	width: number;
	height: number;
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
	size_variants: {
		placeholder: SizeVariantData | null;
		thumb: SizeVariantData | null;
		thumb2x: SizeVariantData | null;
		small: SizeVariantData | null;
		small2x: SizeVariantData | null;
		medium: SizeVariantData | null;
		medium2x: SizeVariantData | null;
		original: {
			width: number;
			height: number;
		};
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
