import axios, { type AxiosRequestConfig, type AxiosRequestHeaders, type AxiosResponse } from "axios";
import CSRF from "./csrf-getter";
// import { setupCache } from "axios-cache-interceptor/dev";
import { setupCache } from "axios-cache-interceptor";

let currentRedirectionURL = "";

window.addEventListener("unload", () => {
	currentRedirectionURL = "";
});

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
					console.log(error);
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
					["Password required", "Password is invalid", "Album is not enabled for password-based access", "Login required."].find(
						(e) => e === error.response?.data?.message,
					) !== undefined
				) {
					return Promise.reject(error);
				}

				if (error.response && error.response.status && !isNaN(error.response.status)) {
					let errorMsg: string;
					if (error.response.data.detail && error.response.status) {
						errorMsg = `Status: ${error.response.status}, ${error.response.data.detail}`;
					} else {
						errorMsg = error.message;
					}
					if (error.response.status == 419 && currentRedirectionURL === "") {
						alert("Session timed out! Click ok to get redirected to the main page!");
						window.location.href = "/";
						currentRedirectionURL = "/";
					}
					if (error.response.status !== 404) {
						const event = new CustomEvent("error", { detail: error.response.data });
						window.dispatchEvent(event);
					}
				}

				return Promise.reject(error);
			},
		);
	},
	handleError(error: any) {
		if (error.response) {
			console.log(error.response.data);
			console.log(error.response.status);
			console.log(error.response.headers);
		} else if (error.request) {
			console.log(error.request);
		} else {
			console.log("Error", error.message);
			console.log(error);
		}
	},
};

export default AxiosConfig;
