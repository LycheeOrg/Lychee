import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { type Ref } from "vue";

export type Uploadable = {
	file: File;
	status: "uploading" | "waiting" | "done" | "error" | "warning";
};

export function useMouseEvents(can_upload: Ref<boolean>, is_upload_visible: Ref<boolean>, list_upload_files: Ref<Uploadable[]>) {
	function dragEnd(e: DragEvent) {
		if (can_upload.value !== true) {
			return;
		}
		// prevent default action (open as a link for some elements)
		e.preventDefault();
	}

	function dropUpload(e: DragEvent) {
		if (can_upload.value !== true) {
			return;
		}
		// prevent default action (open as a link for some elements)
		e.preventDefault();

		if (e.dataTransfer === null) {
			return;
		}

		if (e.dataTransfer.files.length > 0) {
			for (let i = 0; i < e.dataTransfer.files.length; i++) {
				list_upload_files.value.push({ file: e.dataTransfer.files[i], status: "waiting" });
			}
			is_upload_visible.value = true;
		} else if (e.dataTransfer.getData("Text").length > 3) {
			// handle url upload here
			// upload.start.url(e.originalEvent.dataTransfer.getData('Text'));
		}
		return false;
	}

	function onPaste(e: ClipboardEvent) {
		if (shouldIgnoreKeystroke()) {
			return;
		}

		if (can_upload.value !== true) {
			return;
		}

		if (e.clipboardData === null) {
			return;
		}
		if (e.clipboardData.items) {
			const items = e.clipboardData.items;

			// Search clipboard items for an image
			for (let i = 0; i < items.length; i++) {
				if (items[i].type.indexOf("image") !== -1 || items[i].type.indexOf("video") !== -1) {
					const file = items[i].getAsFile();
					if (file) {
						list_upload_files.value.push({ file: file, status: "waiting" });
					}
				}
			}

			if (list_upload_files.value.length > 0) {
				is_upload_visible.value = true;
				return false;
			}
		}
	}

	return {
		dragEnd,
		dropUpload,
		onPaste,
	};
}
