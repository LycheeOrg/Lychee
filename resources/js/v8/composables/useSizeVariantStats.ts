/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/* istanbul ignore next @preserve */
function sizeVariantToColour(sv: App.Enum.SizeVariantType): string {
	const documentStyle = getComputedStyle(document.body);
	switch (sv) {
		// raw
		case 0:
			return documentStyle.getPropertyValue("--ui-color-primary-800");
		// original
		case 1:
			return documentStyle.getPropertyValue("--ui-color-primary-700");
		// medium2x
		case 2:
			return documentStyle.getPropertyValue("--ui-color-primary-600");
		// medium
		case 3:
			return documentStyle.getPropertyValue("--ui-color-primary-500");
		// small2x
		case 4:
			return documentStyle.getPropertyValue("--ui-color-primary-400");
		// small
		case 5:
			return documentStyle.getPropertyValue("--ui-color-primary-300");
		// thumb2x
		case 6:
			return documentStyle.getPropertyValue("--ui-color-primary-200");
		// thumb
		case 7:
			return documentStyle.getPropertyValue("--ui-color-primary-100");
		// placeholder
		case 8:
			return documentStyle.getPropertyValue("--ui-color-primary-50");
	}
}

function sizeToUnit(bytes: number): string {
	if (bytes === 0) return "0 B";

	const symbols = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
	let pow = Math.floor(Math.log(bytes) / Math.log(1024));

	if (pow >= symbols.length) {
		// if the number is too large, we fall back to the largest available symbol
		pow = symbols.length - 1;
	}
	const readableSize = (bytes / Math.pow(1024, pow)).toFixed(2);

	return `${readableSize} ${symbols[pow]}`;
}

export function useSizeVariantStats() {
	return { sizeVariantToColour, sizeToUnit };
}
