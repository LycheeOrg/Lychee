import axios, { AxiosError, InternalAxiosRequestConfig, type AxiosResponse } from "axios";
import CSRF from "./csrf-getter";
// import { setupCache } from "axios-cache-interceptor/dev";
import { setupCache } from "axios-cache-interceptor";

/**
 * Requests made with `responseType: "blob"` (thumb assets, zip/photo downloads) still get their
 * error bodies parsed as a `Blob` by axios, even though the server actually sent a JSON error
 * payload - so `error.response.data` is a `Blob`, not the `{ message }` object callers expect.
 * Read it back out as text/JSON before anything tries to use it, otherwise a raw `Blob` ends up
 * in the global `"error"` CustomEvent's `detail` and gets rendered as the literal string
 * "[object Blob]" by Error.vue's `v-html` fallback.
 */
async function extractErrorData(data: unknown): Promise<{ message?: string } & Record<string, unknown>> {
	if (typeof Blob !== "undefined" && data instanceof Blob) {
		try {
			return JSON.parse(await data.text()) as { message?: string };
		} catch (_error) {
			return {};
		}
	}
	return (data ?? {}) as { message?: string };
}

const AxiosConfig = {
	axiosSetUp() {
		setupCache(axios);
		axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
		axios.interceptors.request.use(
			function (config: InternalAxiosRequestConfig) {
				try {
					const token = CSRF.get();
					config.headers["X-XSRF-TOKEN"] = token;
				} catch (_error) {
					// Cookie expired!
					// const event = new CustomEvent("session_expired");
					// window.dispatchEvent(event);
					// We reject to ensure that the request is not even sent.
					// return Promise.reject("session_expired");
				}

				config.headers["Content-Type"] = "application/json";
				return config;
			},
			function (error: AxiosError) {
				return Promise.reject(error);
			},
		);

		axios.interceptors.response.use(
			function (response: AxiosResponse): AxiosResponse {
				return response;
			},
			async function (error: AxiosError): Promise<never> {
				if (!error.response) {
					return Promise.reject(error);
				}

				const data = await extractErrorData(error.response.data);
				const message = data.message || "An error occurred";

				if (
					data.message &&
					["Password required", "Password is invalid", "Album is not enabled for password-based access", "Login required."].find(
						(e) => e === message,
					) !== undefined
				) {
					return Promise.reject(error);
				}

				if (error.response.status === 419) {
					const event = new CustomEvent("session_expired");
					window.dispatchEvent(event);
					return Promise.reject(error);
				}

				// Blob-typed requests (thumb assets, zip/photo downloads) degrade gracefully on
				// their own (e.g. Thumb.vue falls back to a placeholder icon) - surfacing a
				// global error overlay on top of that would just be noise for something the
				// caller already handles silently.
				const isBlobRequest = error.config?.responseType === "blob";

				if (error.response.status && !isNaN(error.response.status) && error.response.status !== 404 && !isBlobRequest) {
					const event = new CustomEvent("error", { detail: data });
					window.dispatchEvent(event);
				}

				return Promise.reject(error);
			},
		);
	},
};

export default AxiosConfig;
