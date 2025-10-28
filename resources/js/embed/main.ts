import { createApp } from "vue";
import EmbedWidget from "./components/EmbedWidget.vue";
import { validateConfig, parseDataAttributes } from "./config";
import type { EmbedConfig } from "./types";

/**
 * Initialize a single Lychee embed widget
 *
 * @param element HTMLElement or CSS selector where widget should be mounted
 * @param config Widget configuration
 * @returns Vue app instance
 */
export function createLycheeEmbed(element: HTMLElement | string, config: Partial<EmbedConfig>) {
	// Resolve element
	const targetElement = typeof element === "string" ? document.querySelector<HTMLElement>(element) : element;

	if (!targetElement) {
		throw new Error(`Element not found: ${element}`);
	}

	// Parse data attributes from element (if config not fully provided)
	const dataConfig = parseDataAttributes(targetElement);
	const mergedConfig = { ...dataConfig, ...config };

	// Validate and normalize configuration
	const validatedConfig = validateConfig(mergedConfig);

	// Create Vue app instance
	const app = createApp(EmbedWidget, {
		config: validatedConfig,
	});

	// Mount to element
	app.mount(targetElement);

	return app;
}

/**
 * Automatically initialize all Lychee embed widgets on the page
 *
 * Looks for elements with class "lychee-embed" or data-lychee-embed attribute
 * and initializes widgets based on their data attributes.
 */
export function initLycheeEmbeds() {
	// Find all embed containers
	const elements = document.querySelectorAll<HTMLElement>("[data-lychee-embed], .lychee-embed-auto");

	const apps: any[] = [];

	elements.forEach((element) => {
		try {
			// Parse configuration from data attributes
			const config = parseDataAttributes(element);

			// Skip if required attributes are missing
			if (!config.apiUrl || !config.albumId) {
				console.error("Lychee Embed: Missing required data attributes (data-api-url and data-album-id)", element);
				return;
			}

			// Initialize widget
			const app = createLycheeEmbed(element, config);
			apps.push(app);
		} catch (error) {
			console.error("Failed to initialize Lychee Embed:", error, element);
		}
	});

	return apps;
}

// Auto-initialize when DOM is ready (if script is loaded normally)
if (typeof window !== "undefined") {
	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", initLycheeEmbeds);
	} else {
		// DOM already loaded
		initLycheeEmbeds();
	}
}

// Export types for TypeScript users
export type { EmbedConfig, LayoutType, Photo, Album } from "./types";

// Export utilities for advanced users
export { validateConfig, parseDataAttributes } from "./config";
export { createApiClient } from "./api";
export {
	calculateColumns,
	calculateColumnsFromBreakpoints,
	distributeColumnWidths,
	calculateJustifiedRowHeight,
	getAspectRatio,
} from "./utils/columns";
export { layoutSquare, layoutMasonry, layoutGrid, layoutJustified, layoutFilmstrip, filmstripToLayoutResult } from "./layouts";
