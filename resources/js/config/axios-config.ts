import axios, { AxiosError, InternalAxiosRequestConfig, type AxiosResponse } from "axios";
import CSRF from "./csrf-getter";
// import { setupCache } from "axios-cache-interceptor/dev";
import { setupCache } from "axios-cache-interceptor";

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
			function (error: AxiosError<{ message: string }>): Promise<never> {
				if (!error.response) {
					return Promise.reject(error);
				}

				const message = error.response.data.message || "An error occurred";

				if (
					error.response.data.message &&
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

				if (error.response.status && !isNaN(error.response.status) && error.response.status !== 404) {
					const event = new CustomEvent("error", { detail: error.response.data });
					window.dispatchEvent(event);
				}

				return Promise.reject(error);
			},
		);
	},
};

export default AxiosConfig;
