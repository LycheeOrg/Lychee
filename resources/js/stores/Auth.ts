import AuthService from "@/services/auth-service";
import OauthService from "@/services/oauth-service";
import { defineStore } from "pinia";

export type AuthStore = ReturnType<typeof useAuthStore>;

export type OauthProvider = {
	url: string;
	icon: string;
	provider: App.Enum.OauthProvidersType;
};

function mapToOauths(provider: App.Enum.OauthProvidersType): OauthProvider {
	let icon = OauthService.providerIcon(provider);
	let url = `/auth/${provider}/authenticate`;
	return { url, icon, provider };
}

export const useAuthStore = defineStore("auth", {
	state: () => ({
		user: null as App.Http.Resources.Models.UserResource | null,
		oauthData: undefined as OauthProvider[] | undefined,
	}),
	actions: {
		async getUser(): Promise<App.Http.Resources.Models.UserResource> {
			if (this.user === null) {
				await AuthService.user()
					.then((response) => {
						this.user = response.data;
					})
					.catch((error) => {
						console.error(error);
						this.user = null;
						throw error;
					});
			}
			return this.user as App.Http.Resources.Models.UserResource;
		},

		async getOauthData(): Promise<OauthProvider[]> {
			if (this.oauthData === undefined) {
				await OauthService.listProviders()
					.then((response) => {
						this.oauthData = response.data.map(mapToOauths);
					})
					.catch((error) => {
						console.error(error);
						this.oauthData = undefined;
						throw error;
					});
			}
			return this.oauthData as OauthProvider[];
		},

		setUser(user: App.Http.Resources.Models.UserResource | null) {
			this.user = user;
		},
	},
});
