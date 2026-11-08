/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */
import { afterEach, describe, expect, it, vi } from "vite-plus/test";
import axios, { AxiosError, AxiosHeaders, type InternalAxiosRequestConfig } from "axios";
import AxiosConfig from "./axios-config";

vi.mock("./client-cache", () => ({ isClientCacheEnabled: () => false, setUpClientCache: vi.fn() }));
vi.mock("./csrf-getter", () => ({ default: { get: () => "test-token" } }));
vi.mock("../services/init-service", () => ({ default: { fetchMac: vi.fn() } }));

afterEach(() => {
	axios.interceptors.request.clear();
	axios.interceptors.response.clear();
	vi.restoreAllMocks();
});

describe("password login rate-limit error reporting", () => {
	it.each([
		["post", "/api/v2/Auth::login", 429, false],
		["post", "/gallery/api/v2/Auth::login", 429, false],
		["post", "/api/v2/Auth::login", 500, true],
		["get", "/api/v2/Auth::login", 429, true],
		["post", "/api/v2/Gallery::unlock", 429, true],
		["post", "/api/v2/Auth::login-extra", 429, true],
	])("%s %s status %i: global error=%s", async (method, url, status, report) => {
		AxiosConfig.axiosSetUp();
		const listener = vi.fn();
		window.addEventListener("error", listener);
		let rejection: AxiosError | undefined;
		try {
			await expect(
				axios.request({
					method,
					url,
					adapter: async (config: InternalAxiosRequestConfig) => {
						rejection = new AxiosError("Request failed", undefined, config, undefined, {
							status,
							statusText: "Error",
							data: { message: "Request failed" },
							headers: new AxiosHeaders(),
							config,
						});
						throw rejection;
					},
				}),
			).rejects.toBeInstanceOf(AxiosError);
			expect(rejection).toBeDefined();
			expect(listener).toHaveBeenCalledTimes(report ? 1 : 0);
		} finally {
			window.removeEventListener("error", listener);
		}
	});
});
