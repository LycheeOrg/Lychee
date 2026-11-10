/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/** Only URLs from `URL.createObjectURL()` may be revoked (Feature 113, FR-113-02). */
export function isObjectUrl(url: string): boolean {
	return url.startsWith("blob:");
}

/**
 * Turns a `/Asset` or `/Faces/{id}/photo/{variant}` body (fetched as a blob) into a URL `<img src>`
 * can load: the S3 URL of an `AssetUrlResource` JSON body (Feature 113, FR-113-01, ADR-113-01),
 * otherwise an object URL of the bytes. Rejects a JSON body without a string `url`.
 */
export async function assetBlobToUrl(blob: Blob): Promise<string> {
	if (!blob.type.startsWith("application/json")) {
		return URL.createObjectURL(blob);
	}
	const body = JSON.parse(await blob.text()) as Partial<App.Http.Resources.V3.AssetUrlResource> | null;
	if (typeof body?.url !== "string") {
		throw new Error("Asset response without a URL");
	}
	return body.url;
}
