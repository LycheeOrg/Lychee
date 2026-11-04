/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it } from "vite-plus/test";
import { bufferToBase64Url } from "@/vendor/webauthn/base64url";

/**
 * Feature 107, FR-107-10 / S-107-24: the server (web-auth/webauthn-lib) only
 * accepts unpadded base64url for clientDataJSON, authenticatorData and signature.
 */
describe("bufferToBase64Url", () => {
	it("uses the URL-safe alphabet", () => {
		// 0xfb 0xff 0xbf encodes to "+/+/" in standard base64.
		expect(bufferToBase64Url(new Uint8Array([0xfb, 0xff, 0xbf]))).toBe("-_-_");
	});

	it("drops the padding", () => {
		expect(bufferToBase64Url(new Uint8Array([0x61]))).toBe("YQ");
		expect(bufferToBase64Url(new Uint8Array([0x61, 0x62]))).toBe("YWI");
		expect(bufferToBase64Url(new Uint8Array([0x61, 0x62, 0x63]))).toBe("YWJj");
	});

	it("accepts an ArrayBuffer", () => {
		expect(bufferToBase64Url(new Uint8Array([0x61, 0x62, 0x63]).buffer)).toBe("YWJj");
	});

	it("encodes an empty buffer as an empty string", () => {
		expect(bufferToBase64Url(new ArrayBuffer(0))).toBe("");
	});

	it("handles buffers larger than the argument limit of String.fromCharCode", () => {
		const bytes = new Uint8Array(200_000);
		for (let i = 0; i < bytes.length; i++) {
			bytes[i] = (i * 31) & 0xff;
		}
		let binary = "";
		for (const byte of bytes) {
			binary += String.fromCharCode(byte);
		}
		const expected = btoa(binary).replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/, "");

		expect(bufferToBase64Url(bytes)).toBe(expected);
	});
});
