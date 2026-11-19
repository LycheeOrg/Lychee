/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/** Bytes per `String.fromCharCode` call, below every engine's argument limit. */
const CHUNK_SIZE = 0x8000;

/**
 * Unpadded base64url (RFC 4648 §5) of a byte buffer: the encoding of the
 * WebAuthn JSON form, which the server expects (Feature 107, FR-107-10).
 */
export function bufferToBase64Url(buffer: ArrayBuffer | Uint8Array): string {
	const bytes = buffer instanceof Uint8Array ? buffer : new Uint8Array(buffer);
	let binary = "";
	for (let offset = 0; offset < bytes.length; offset += CHUNK_SIZE) {
		binary += String.fromCharCode(...bytes.subarray(offset, offset + CHUNK_SIZE));
	}

	return btoa(binary).replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/, "");
}
