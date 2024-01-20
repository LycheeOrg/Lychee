import { Alpine } from "alpinejs";
import { DropboxView } from "./types";

type DropboxFile = {
	bytes: number;
	icon: string;
	id: string;
	isDir: boolean;
	link: string;
	linkType: string;
	name: string;
	thumbnailLink: string;
};

export const dropboxView = (Alpine: Alpine) =>
	Alpine.data(
		"dropboxView",
		// @ts-expect-error
		(urlArea_: string, progress_: string): DropboxView => ({
			urlArea: urlArea_,
			progress: progress_,

			chooseFromDropbox(): void {
				// @ts-expect-error
				Dropbox.choose({
					linkType: "direct",
					multiselect: true,
					success: (files: DropboxFile[]) => {
						this.urlArea = files.map((file) => file.link).join("\n");
					},
				});
			},

			send(): void {
				Livewire.dispatch("notify", [{ type: "info", msg: this.progress }]);
				// @ts-expect-error
				this.$wire.submit();
			},
		}),
	);
