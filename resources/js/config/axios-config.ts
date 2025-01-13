import axios, { type AxiosRequestConfig, type AxiosRequestHeaders, type AxiosResponse } from "axios";
import CSRF from "./csrf-getter";
// import { setupCache } from "axios-cache-interceptor/dev";
import { setupCache } from "axios-cache-interceptor";

const AxiosConfig = {
	axiosSetUp() {
		// setupCache(axios, { debug: console.log });
		setupCache(axios);

		axios.interceptors.request.use(
			// @ts-expect-error
			function (config: AxiosRequestConfig) {
				try {
					const token = CSRF.get();
					(config.headers as AxiosRequestHeaders)["X-XSRF-TOKEN"] = token;
				} catch (error) {
					// Cookie expired!
					// const event = new CustomEvent("session_expired");
					// window.dispatchEvent(event);
					// We reject to ensure that the request is not even sent.
					// return Promise.reject("session_expired");
				}

				(config.headers as AxiosRequestHeaders)["Content-Type"] = "application/json";
				return config;
			},
			function (error: any) {
				return Promise.reject(error);
			},
		);

		axios.interceptors.response.use(
			function (response: AxiosResponse): AxiosResponse {
				return response;
			},
			function (error: any): Promise<never> {
				if (
					error.response?.data?.message &&
					["Password required", "Password is invalid", "Album is not enabled for password-based access", "Login required."].find(
						(e) => e === error.response.data.message,
					) !== undefined
				) {
					return Promise.reject(error);
				}

				if (error.response && error.response.status === 419) {
					const event = new CustomEvent("session_expired");
					window.dispatchEvent(event);
					return Promise.reject(error);
				}

				if (error.response && error.response.status && !isNaN(error.response.status) && error.response.status !== 404) {
					const event = new CustomEvent("error", { detail: error.response.data });
					window.dispatchEvent(event);
				}

				return Promise.reject(error);
			},
		);
	},
};

export default AxiosConfig;
