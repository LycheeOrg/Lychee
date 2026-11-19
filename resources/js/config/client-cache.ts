/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import type { AxiosInstance, InternalAxiosRequestConfig } from "axios";
import { setupCache } from "axios-cache-interceptor";

/**
 * Client-side cache switch chosen by the server.
 *
 * `Meta` renders `<meta name="lychee-client-cache" content="0|1">` from `CLIENT_CACHE_ENABLED`,
 * so the value is known before the first request. The tag is read once and cached; only
 * `content="0"` turns the cache off, a missing tag means on (the flag's default).
 */
let cached: boolean | undefined = undefined;

export function parseClientCacheEnabled(meta: Element | null): boolean {
	return meta?.getAttribute("content") !== "0";
}

export function isClientCacheEnabled(): boolean {
	cached ??= parseClientCacheEnabled(document.querySelector('meta[name="lychee-client-cache"]'));
	return cached;
}

// The headers axios-cache-interceptor's `cacheTakeover` adds to every request when enabled: they
// keep the browser HTTP cache from answering, which a disabled interceptor no longer does.
function addNoCacheHeaders(config: InternalAxiosRequestConfig): InternalAxiosRequestConfig {
	config.headers.set("Cache-Control", "no-cache, no-store, must-revalidate, max-age=0", false);
	config.headers.set("Pragma", "no-cache", false);
	config.headers.set("Expires", "0", false);
	return config;
}

/**
 * Installs the response cache on `instance`. Disabled, it stores and serves nothing but keeps its
 * storage, so `clearCache()` callers still work (they just remove nothing).
 */
export function setUpClientCache(instance: AxiosInstance, enabled: boolean): void {
	setupCache(instance, { enabled: enabled });
	if (!enabled) {
		instance.interceptors.request.use(addNoCacheHeaders);
	}
}
