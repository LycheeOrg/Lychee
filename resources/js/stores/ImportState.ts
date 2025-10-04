import { defineStore } from "pinia";
import ImportService from "@/services/import-service";

export type ImportState = ReturnType<typeof useImportState>;

export const useImportState = defineStore("import-store", {
	state: () => ({
		options: undefined as App.Http.Resources.Admin.ImportFromServerOptionsResource | undefined,
		directory: "" as string,
	}),
	actions: {
		refresh(): Promise<void> {
			this.reset();
			return this.load();
		},
		reset() {
			this.options = undefined;
			this.directory = "";
		},
		load(): Promise<void> {
			if (this.options !== undefined) {
				return Promise.resolve();
			}

			return ImportService.getOptions().then((response) => {
				this.options = response.data;
				this.directory = response.data.directory;
			});
		},
	},
});
