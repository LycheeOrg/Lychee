/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

const encoder = new TextEncoder();

/**
 * Length of a description as the server's DescriptionRule measures it:
 * UTF-8 bytes, like PHP `strlen`.
 */
export function descriptionLength(value: string | null | undefined): number {
	return value ? encoder.encode(value).length : 0;
}

/** True when the server would reject a description of this length. */
export function isDescriptionTooLong(length: number, max: number): boolean {
	return length > max;
}
