import AuthService from "@/services/auth-service";
import { defineStore } from "pinia";

export type AuthStore = ReturnType<typeof useAuthStore>;

export const useAuthStore = defineStore("auth", {
	state: () => ({
		user: null as App.Http.Resources.Models.UserResource | null,
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

		setUser(user: App.Http.Resources.Models.UserResource | null) {
			this.user = user;
		},
	},
});
