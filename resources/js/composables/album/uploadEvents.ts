import { handleFolderDrop, hasDirectoryEntry } from "@/composables/album/folderDrop";
import { useRandomId } from "@/composables/useRandomId";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { useToast } from "primevue/usetoast";
import { ref, type Ref } from "vue";

export type Uploadable = {
	uid: string;
	file: File;
	album_id?: string;
	albumTitle?: string;
	message?: string;
	status: "uploading" | "waiting" | "done" | "error" | "warning";
};

export function useMouseEvents(
	can_upload: Ref<boolean>,
	is_upload_visible: Ref<boolean>,
	list_upload_files: Ref<Uploadable[]>,
	parent_id: Ref<string | null> = ref(null),
	existingAlbums: Ref<{ id: string; title: string }[]> = ref([]),
	upload_config: Ref<App.Http.Resources.GalleryConfigs.UploadConfig | undefined> = ref(undefined),
) {
	const toast = useToast();
	const generateId = useRandomId();

	function onError(message: string) {
		toast.add({ severity: "error", summary: "Upload error", detail: message, life: 5000 });
	}

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

		// Folder drop path: enabled by default; disabled only when explicitly set to false.
		if (
			upload_config.value !== undefined &&
			upload_config.value.folder_upload_enabled === true &&
			e.dataTransfer.items &&
			hasDirectoryEntry(e.dataTransfer.items)
		) {
			const maxDepth = upload_config.value.folder_upload_max_depth;
			handleFolderDrop(e.dataTransfer.items, parent_id.value, existingAlbums.value, list_upload_files, maxDepth, onError).then((queued) => {
				if (queued) {
					is_upload_visible.value = true;
				}
			});
			return;
		}

		// Flat-file path (unchanged).
		if (e.dataTransfer.files.length > 0) {
			for (let i = 0; i < e.dataTransfer.files.length; i++) {
				list_upload_files.value.push({ uid: generateId(), file: e.dataTransfer.files[i], status: "waiting" });
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
						list_upload_files.value.push({ uid: generateId(), file: file, status: "waiting" });
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
