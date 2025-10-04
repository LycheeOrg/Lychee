import { defineStore } from "pinia";

export type NsfwConsentedStore = ReturnType<typeof useNsfwConsentedStore>;

export const useNsfwConsentedStore = defineStore("nsfw-consented-store", {
	state: () => ({
		nsfw_consented: [] as string[],
	}),
	actions: {
		consent(id: string) {
			this.nsfw_consented.push(id);
		},
	},
	getters: {
		hasConsented(state): (id: string) => boolean {
			return (id: string) => state.nsfw_consented.includes(id);
		},
	},
});
