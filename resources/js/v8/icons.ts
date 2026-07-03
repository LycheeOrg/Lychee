import { addCollection } from "@iconify/vue";
import { icons as primeIcons } from "@iconify-json/prime";

/**
 * Registers the prime Iconify collection offline so icon lookups never hit
 * the public Iconify API at runtime - see Feature 049 T-049-03.
 */
export function registerIconCollections(): void {
	addCollection(primeIcons);
}

/**
 * Converts a v7 PrimeIcons class string ("pi pi-home") to the Iconify name
 * ("prime:home") consumed by UIcon, per Feature 049 FR-049-15.
 */
export function primeIconToIconifyName(icon: string): string {
	return "prime:" + icon.replace(/^pi\s+pi-/, "").replace(/^pi-/, "");
}
