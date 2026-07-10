import { addCollection } from "@iconify/vue";
import { icons as primeIcons } from "@iconify-json/prime";
import { icons as lucideIcons } from "@iconify-json/lucide";

/**
 * Registers Iconify collections offline so icon lookups never hit the public
 * Iconify API at runtime - see Feature 049 T-049-03. Lychee must be usable
 * with zero network connection, so any new icon set must be added here as a
 * local @iconify-json/* dependency rather than resolved remotely.
 */
export function registerIconCollections(): void {
	addCollection(primeIcons);
	addCollection(lucideIcons);
}

/**
 * Converts a v7 PrimeIcons class string ("pi pi-home") to the Iconify name
 * ("prime:home") consumed by UIcon, per Feature 049 FR-049-15.
 */
export function primeIconToIconifyName(icon: string): string {
	return "prime:" + icon.replace(/^pi\s+pi-/, "").replace(/^pi-/, "");
}
