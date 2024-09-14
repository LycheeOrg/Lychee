import axios, { type AxiosRequestConfig, type AxiosRequestHeaders, type AxiosResponse } from "axios";
import CSRF from "./csrf-getter";
import { setupCache } from "axios-cache-interceptor/dev";

const AxiosConfig = {
	axiosSetUp() {
		setupCache(axios, { debug: console.log }),
			axios.interceptors.request.use(
				// @ts-expect-error
				function (config: AxiosRequestConfig) {
					const token = CSRF.get();
					(config.headers as AxiosRequestHeaders)["X-XSRF-TOKEN"] = token;
					(config.headers as AxiosRequestHeaders)["Content-Type"] = "application/json";
					return config;
				},
				function (error: any) {
					return Promise.reject(error);
				},
			);

		axios.interceptors.response.use(
			function (response: AxiosResponse): AxiosResponse {
				if (response && response.status === 201) {
					//   toast.add({severity: 'success', summary: 'Record saved successfully.'});
				}
				return response;
			},
			function (error: any): Promise<never> {
				if (error.response && error.response.status && !isNaN(error.response.status)) {
					if (error.response.status === 403) {
						// toast.add({severity: 'error' , summary: 'You do not have permission to access this resource.'});
					} else {
						let errorMsg = "";
						if (error.response.data.detail && error.response.status) {
							errorMsg = `Status: ${error.response.status}, ${error.response.data.detail}`;
						} else {
							errorMsg = error.message;
						}
						const event = new CustomEvent("error", { detail: error.response.data });
						window.dispatchEvent(event);
						// toast.add({severity: 'error', summary: errorMsg });
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
