import { defineStore } from "pinia";

export type FlowStateStore = ReturnType<typeof useFlowStateStore>;

export const useFlowStateStore = defineStore("flow-store", {
	state: () => ({
		are_nsfw_blurred: false,
		are_nsfw_consented: false,
	}),
});
