/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { formatAperture, formatDuration, formatFocal, formatNumber, formatShutter } from "../format";

export type ExposureField = "iso" | "focal" | "shutter" | "aperture" | "video_length";

/** How a value of each exposure field is written (FR-085-13). */
export function exposureFormatter(field: ExposureField): (value: number) => string {
	switch (field) {
		case "iso":
			return (value) => formatNumber(value);
		case "focal":
			return formatFocal;
		case "shutter":
			return formatShutter;
		case "aperture":
			return formatAperture;
		case "video_length":
			return formatDuration;
	}
}
