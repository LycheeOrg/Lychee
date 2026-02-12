import type { EmbedConfig, LayoutType, HeaderPlacement, EmbedMode } from "./types";

/**
 * Default configuration values for the embed widget
 */
export const DEFAULT_CONFIG: Partial<EmbedConfig> = {
	mode: "album",
	layout: "justified",
	width: "100%",
	height: "auto",
	spacing: 8,
	targetRowHeight: 200,
	targetColumnWidth: 300,
	maxPhotos: 18,
	sortOrder: "desc",
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

	// Validate mode
	const validModes: EmbedMode[] = ["album", "stream"];
	const mode = config.mode ?? DEFAULT_CONFIG.mode!;
	if (!validModes.includes(mode)) {
		throw new Error(`Invalid mode: ${mode}. Valid modes: ${validModes.join(", ")}`);
	}

	// albumId is required only for album mode
	if (mode === "album" && !config.albumId) {
		throw new Error("albumId is required for album mode");
	}

	// Remove trailing slashes from API URL
	const apiUrl = config.apiUrl.replace(/\/+$/, "");

	// Validate layout type
	const validLayouts: LayoutType[] = ["square", "masonry", "grid", "justified", "filmstrip"];
	const layout = config.layout || DEFAULT_CONFIG.layout!;
	if (!validLayouts.includes(layout)) {
		throw new Error(`Invalid layout: ${layout}. Valid layouts: ${validLayouts.join(", ")}`);
	}

	// Validate numeric values
	const spacing = typeof config.spacing === "number" && Number.isFinite(config.spacing) ? config.spacing : DEFAULT_CONFIG.spacing!;
	if (spacing < 0) {
		throw new Error("spacing must be non-negative");
	}

	const targetRowHeight =
		typeof config.targetRowHeight === "number" && Number.isFinite(config.targetRowHeight)
			? config.targetRowHeight
			: DEFAULT_CONFIG.targetRowHeight!;
	if (targetRowHeight < 100 || targetRowHeight > 1000) {
		throw new Error("targetRowHeight must be between 100 and 1000");
	}

	const targetColumnWidth =
		typeof config.targetColumnWidth === "number" && Number.isFinite(config.targetColumnWidth)
			? config.targetColumnWidth
			: DEFAULT_CONFIG.targetColumnWidth!;
	if (targetColumnWidth < 100 || targetColumnWidth > 800) {
		throw new Error("targetColumnWidth must be between 100 and 800");
	}

	const maxPhotos =
		config.maxPhotos === "none"
			? "none"
			: typeof config.maxPhotos === "number" && Number.isFinite(config.maxPhotos)
				? config.maxPhotos
				: (DEFAULT_CONFIG.maxPhotos as number);
	if (maxPhotos !== "none" && (maxPhotos < 1 || maxPhotos > 500)) {
		throw new Error("maxPhotos must be between 1 and 500, or 'none'");
	}

	// Validate sort order
	const validSortOrders = ["asc", "desc"] as const;
	const sortOrder = config.sortOrder ?? DEFAULT_CONFIG.sortOrder!;
	if (sortOrder !== "asc" && sortOrder !== "desc") {
		throw new Error(`Invalid sortOrder: ${sortOrder}. Valid options: ${validSortOrders.join(", ")}`);
	}

	// Validate header placement
	const validPlacements: HeaderPlacement[] = ["top", "bottom", "none"];
	const headerPlacement = config.headerPlacement ?? DEFAULT_CONFIG.headerPlacement!;
	if (!validPlacements.includes(headerPlacement)) {
		throw new Error(`Invalid headerPlacement: ${headerPlacement}. Valid options: ${validPlacements.join(", ")}`);
	}

	return {
		apiUrl,
		mode,
		albumId: config.albumId,
		layout,
		width: config.width ?? DEFAULT_CONFIG.width!,
		height: config.height ?? DEFAULT_CONFIG.height!,
		spacing,
		targetRowHeight,
		targetColumnWidth,
		maxPhotos,
		sortOrder,
		showTitle: config.showTitle ?? DEFAULT_CONFIG.showTitle!,
		showDescription: config.showDescription ?? DEFAULT_CONFIG.showDescription!,
		showCaptions: config.showCaptions ?? DEFAULT_CONFIG.showCaptions!,
		showExif: config.showExif ?? DEFAULT_CONFIG.showExif!,
		headerPlacement,
		author: config.author,
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

	// Mode (optional, defaults to "album")
	if (element.dataset.mode) {
		config.mode = element.dataset.mode as EmbedMode;
	}

	// Album ID (required for album mode, optional for stream mode)
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
		const parsed = parseInt(element.dataset.spacing, 10);
		if (!Number.isNaN(parsed)) {
			config.spacing = parsed;
		}
	}
	if (element.dataset.targetRowHeight) {
		const parsed = parseInt(element.dataset.targetRowHeight, 10);
		if (!Number.isNaN(parsed)) {
			config.targetRowHeight = parsed;
		}
	}
	if (element.dataset.targetColumnWidth) {
		const parsed = parseInt(element.dataset.targetColumnWidth, 10);
		if (!Number.isNaN(parsed)) {
			config.targetColumnWidth = parsed;
		}
	}
	if (element.dataset.maxPhotos) {
		if (element.dataset.maxPhotos === "none") {
			config.maxPhotos = "none";
		} else {
			const parsed = parseInt(element.dataset.maxPhotos, 10);
			if (!Number.isNaN(parsed)) {
				config.maxPhotos = parsed;
			}
		}
	}

	// Sort order
	if (element.dataset.sortOrder) {
		config.sortOrder = element.dataset.sortOrder as "asc" | "desc";
	}

	// Boolean options
	if (element.dataset.showTitle !== undefined) {
		const value = element.dataset.showTitle.trim().toLowerCase();
		config.showTitle = !(value === "false" || value === "0");
	}
	if (element.dataset.showDescription !== undefined) {
		const value = element.dataset.showDescription.trim().toLowerCase();
		config.showDescription = !(value === "false" || value === "0");
	}
	if (element.dataset.showCaptions !== undefined) {
		const value = element.dataset.showCaptions.trim().toLowerCase();
		config.showCaptions = !(value === "false" || value === "0");
	}
	if (element.dataset.showExif !== undefined) {
		const value = element.dataset.showExif.trim().toLowerCase();
		config.showExif = !(value === "false" || value === "0");
	}

	// Header placement
	if (element.dataset.headerPlacement) {
		config.headerPlacement = element.dataset.headerPlacement as HeaderPlacement;
	}

	// Author filter
	if (element.dataset.author) {
		config.author = element.dataset.author;
	}

	// Custom class
	if (element.dataset.containerClass) {
		config.containerClass = element.dataset.containerClass;
	}

	return config;
}
