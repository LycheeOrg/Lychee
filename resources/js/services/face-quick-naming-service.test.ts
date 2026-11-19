/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

// @vitest-environment jsdom
import { beforeEach, describe, expect, it, vi } from "vite-plus/test";
import axios from "axios";
import FaceQuickNamingService from "@/services/face-quick-naming-service";

vi.mock("axios", () => ({
	default: { get: vi.fn() },
}));

const get = vi.mocked(axios.get);

beforeEach(() => {
	get.mockReset();
	URL.createObjectURL = vi.fn(() => "blob:face");
});

describe("FaceQuickNamingService.getPhoto() (Feature 113)", () => {
	it("resolves an S3 JSON body to its URL (S-113-08)", async () => {
		get.mockResolvedValue({ data: new Blob([JSON.stringify({ url: "https://bucket.example/face.jpg" })], { type: "application/json" }) });

		await expect(FaceQuickNamingService.getPhoto("face", "medium")).resolves.toBe("https://bucket.example/face.jpg");
		expect(URL.createObjectURL).not.toHaveBeenCalled();
	});

	it("resolves bytes to an object URL", async () => {
		get.mockResolvedValue({ data: new Blob(["bytes"], { type: "image/jpeg" }) });

		await expect(FaceQuickNamingService.getPhoto("face", "medium")).resolves.toBe("blob:face");
	});
});
