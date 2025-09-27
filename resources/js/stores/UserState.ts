import AuthService from "@/services/auth-service";
import OauthService from "@/services/oauth-service";
import { defineStore } from "pinia";

export type UserStore = ReturnType<typeof useUserStore>;

export type OauthProvider = {
	url: string;
	icon: string;
	provider: App.Enum.OauthProvidersType;
};

function mapToOauths(provider: App.Enum.OauthProvidersType): OauthProvider {
	const icon = OauthService.providerIcon(provider);
	const url = `/auth/${provider}/authenticate`;
	return { url, icon, provider };
}

export const useUserStore = defineStore("user-store", {
	state: () => ({
		// Needed to display the oauth providers on the login page
		oauthData: undefined as OauthProvider[] | undefined,

		// User data.
		user: undefined as App.Http.Resources.Models.UserResource | undefined,
	}),
	actions: {
		async refresh(): Promise<App.Http.Resources.Models.UserResource> {
			this.user = undefined;
			return this.load();
		},
		async load(): Promise<App.Http.Resources.Models.UserResource> {
			if (this.user !== undefined) {
				return Promise.resolve(this.user);
			}

			return AuthService.user()
				.then((response) => {
					this.user = response.data;
					return this.user;
				})
				.catch((error) => {
					console.error(error);
					throw error;
				});
		},
		async getOauthData(): Promise<OauthProvider[]> {
			if (this.oauthData !== undefined) {
				return Promise.resolve(this.oauthData);
			}

			return OauthService.listProviders()
				.then((response) => {
					this.oauthData = response.data.map(mapToOauths);
					return this.oauthData;
				})
				.catch((error) => {
					console.error(error);
					throw error;
				});
		},
		setUser(user: App.Http.Resources.Models.UserResource | undefined) {
			this.user = user;
		},
	},
	getters: {
		isLoggedIn(): boolean {
			return this.user !== undefined && this.user.id !== null;
		},
		isLoaded(): boolean {
			return this.user !== undefined;
		},
		isGuest(): boolean {
			return this.user !== undefined && this.user.id === null;
		},
	},
});
