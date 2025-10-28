import type { EmbedConfig, LayoutType, HeaderPlacement } from "./types";

/**
 * Default configuration values for the embed widget
 */
export const DEFAULT_CONFIG: Partial<EmbedConfig> = {
	layout: "justified",
	width: "100%",
	height: "auto",
	spacing: 8,
	targetRowHeight: 200,
	targetColumnWidth: 300,
	maxPhotos: 15,
	showTitle: true,
	showDescription: true,
	showCaptions: true,
	showExif: true,
	headerPlacement: "top",
};

/**
 * Validate and normalize embed configuration
 *
 * @param config User-provided configuration
 * @returns Normalized configuration with defaults applied
 * @throws Error if required fields are missing or invalid
 */
export function validateConfig(config: Partial<EmbedConfig>): EmbedConfig {
	if (!config.apiUrl) {
		throw new Error("apiUrl is required");
	}

	if (!config.albumId) {
		throw new Error("albumId is required");
	}

	// Remove trailing slash from API URL
	const apiUrl = config.apiUrl.replace(/\/$/, "");

	// Validate layout type
	const validLayouts: LayoutType[] = ["square", "masonry", "grid", "justified", "filmstrip"];
	const layout = config.layout || DEFAULT_CONFIG.layout!;
	if (!validLayouts.includes(layout)) {
		throw new Error(`Invalid layout: ${layout}. Valid layouts: ${validLayouts.join(", ")}`);
	}

	// Validate numeric values
	const spacing = config.spacing ?? DEFAULT_CONFIG.spacing!;
	if (spacing < 0) {
		throw new Error("spacing must be non-negative");
	}

	const targetRowHeight = config.targetRowHeight ?? DEFAULT_CONFIG.targetRowHeight!;
	if (targetRowHeight < 100 || targetRowHeight > 1000) {
		throw new Error("targetRowHeight must be between 100 and 1000");
	}

	const targetColumnWidth = config.targetColumnWidth ?? DEFAULT_CONFIG.targetColumnWidth!;
	if (targetColumnWidth < 100 || targetColumnWidth > 800) {
		throw new Error("targetColumnWidth must be between 100 and 800");
	}

	const maxPhotos = config.maxPhotos ?? DEFAULT_CONFIG.maxPhotos!;
	if (maxPhotos < 1 || maxPhotos > 100) {
		throw new Error("maxPhotos must be between 1 and 100");
	}

	// Validate header placement
	const validPlacements: HeaderPlacement[] = ["top", "bottom", "none"];
	const headerPlacement = config.headerPlacement ?? DEFAULT_CONFIG.headerPlacement!;
	if (!validPlacements.includes(headerPlacement)) {
		throw new Error(`Invalid headerPlacement: ${headerPlacement}. Valid options: ${validPlacements.join(", ")}`);
	}

	return {
		apiUrl,
		albumId: config.albumId,
		layout,
		width: config.width ?? DEFAULT_CONFIG.width!,
		height: config.height ?? DEFAULT_CONFIG.height!,
		spacing,
		targetRowHeight,
		targetColumnWidth,
		maxPhotos,
		showTitle: config.showTitle ?? DEFAULT_CONFIG.showTitle!,
		showDescription: config.showDescription ?? DEFAULT_CONFIG.showDescription!,
		showCaptions: config.showCaptions ?? DEFAULT_CONFIG.showCaptions!,
		showExif: config.showExif ?? DEFAULT_CONFIG.showExif!,
		headerPlacement,
		containerClass: config.containerClass,
	};
}

/**
 * Parse configuration from HTML data attributes
 *
 * @param element HTML element with data-* attributes
 * @returns Partial configuration object
 */
export function parseDataAttributes(element: HTMLElement): Partial<EmbedConfig> {
	const config: Partial<EmbedConfig> = {};

	// Required attributes
	if (element.dataset.apiUrl) {
		config.apiUrl = element.dataset.apiUrl;
	}
	if (element.dataset.albumId) {
		config.albumId = element.dataset.albumId;
	}

	// Layout options
	if (element.dataset.layout) {
		config.layout = element.dataset.layout as LayoutType;
	}

	// Dimensions
	if (element.dataset.width) {
		config.width = element.dataset.width;
	}
	if (element.dataset.height) {
		config.height = element.dataset.height;
	}

	// Numeric options
	if (element.dataset.spacing) {
		config.spacing = parseInt(element.dataset.spacing, 10);
	}
	if (element.dataset.targetRowHeight) {
		config.targetRowHeight = parseInt(element.dataset.targetRowHeight, 10);
	}
	if (element.dataset.targetColumnWidth) {
		config.targetColumnWidth = parseInt(element.dataset.targetColumnWidth, 10);
	}
	if (element.dataset.maxPhotos) {
		config.maxPhotos = parseInt(element.dataset.maxPhotos, 10);
	}

	// Boolean options
	if (element.dataset.showTitle !== undefined) {
		config.showTitle = element.dataset.showTitle !== "false";
	}
	if (element.dataset.showDescription !== undefined) {
		config.showDescription = element.dataset.showDescription !== "false";
	}
	if (element.dataset.showCaptions !== undefined) {
		config.showCaptions = element.dataset.showCaptions !== "false";
	}
	if (element.dataset.showExif !== undefined) {
		config.showExif = element.dataset.showExif !== "false";
	}

	// Header placement
	if (element.dataset.headerPlacement) {
		config.headerPlacement = element.dataset.headerPlacement as HeaderPlacement;
	}

	// Custom class
	if (element.dataset.containerClass) {
		config.containerClass = element.dataset.containerClass;
	}

	return config;
}
