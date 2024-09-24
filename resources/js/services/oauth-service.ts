import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const OauthService = {
	list(): Promise<AxiosResponse<App.Http.Resources.Oauth.OauthRegistrationData[] | App.Enum.OauthProvidersType[]>> {
		return axios.get(`${Constants.API_URL}Oauth`, { data: {} });
	},

	providerIcon(provider: App.Enum.OauthProvidersType): string {
		switch (provider) {
			case "apple":
				return "fa-brands fa-apple";
			case "amazon":
				return "fa-brands fa-amazon";
			case "facebook":
				return "fa-brands fa-facebook";
			case "github":
				return "fa-brands fa-github";
			case "google":
				return "fa-brands fa-google";
			case "mastodon":
				return "fa-brands fa-mastodon";
			case "microsoft":
				return "fa-brands fa-microsoft";
			case "nextcloud":
				return "fa-solid fa-cloud";
			case "keycloak":
				return "fa-solid fa-key";
		}
	},

	clear(provider: string): Promise<AxiosResponse> {
		return axios.delete(`${Constants.API_URL}Oauth`, { data: { provider: provider } });
	},
};

export default OauthService;
