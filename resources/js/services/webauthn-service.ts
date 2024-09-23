import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";
import WebAuthn from "@/vendor/webauthn/webauthn";

type LoginParam = {
	username?: string;
	user_id?: number;
};

const WebAuthnService = {
	get(): Promise<AxiosResponse<App.Http.Resources.Models.WebAuthnResource[]>> {
		return axios.get(`${Constants.API_URL}WebAuthn`, { data: {} });
	},

	edit(id: string, alias: string): Promise<AxiosResponse> {
		return axios.patch(`${Constants.API_URL}WebAuthn`, {
			id: id,
			alias: alias,
		});
	},

	delete(id: string): Promise<AxiosResponse<App.Http.Resources.Models.UserManagementResource>> {
		return axios.post(`${Constants.API_URL}WebAuthn::delete`, { id: id });
	},

	login(username: string | null, user_id: number | null): Promise<JSON | ReadableStream> {
		const params: LoginParam = {};

		if (username !== "" && username !== null) {
			params.username = username;
		} else if (user_id !== null) {
			params.user_id = user_id;
		}

		return new WebAuthn(
			{ login: `${Constants.API_URL}WebAuthn::login`, loginOptions: `${Constants.API_URL}WebAuthn::login/options` },
			{},
			false,
		).login(params);
	},

	register(): Promise<JSON | ReadableStream> {
		return new WebAuthn(
			{
				register: `${Constants.API_URL}WebAuthn::register`,
				registerOptions: `${Constants.API_URL}WebAuthn::register/options`,
			},
			{},
			false,
		).register();
	},

	isWebAuthnUnavailable() {
		return !window.isSecureContext && window.location.hostname !== "localhost" && window.location.hostname !== "127.0.0.1";
	},
};

export default WebAuthnService;
