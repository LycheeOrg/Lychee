<template>
	<Fieldset v-if="albumStore.album" class="w-full">
		<template #legend>
			<span class="flex items-center gap-2"><UIcon :name="legendIcon" />{{ legendLabel }}</span>
		</template>
		<div class="flex flex-col gap-4">
			<UButton
				icon="lucide:plus"
				color="primary"
				variant="solid"
				class="font-bold justify-center"
				:label="$t('gallery.album.tracks.add')"
				:loading="uploading"
				@click="triggerFileInput"
			/>
			<input ref="fileInput" type="file" accept=".gpx" multiple class="hidden" @change="onFilesSelected" />

			<div v-if="tracks.length === 0" class="text-muted text-center py-3">
				{{ $t("gallery.album.tracks.empty") }}
			</div>
			<ul v-else class="flex flex-col gap-2">
				<li v-for="track in tracks" :key="track.id" class="flex items-center gap-2 border border-default rounded-md px-3 py-2">
					<UIcon name="lucide:route" class="shrink-0 text-muted" />
					<template v-if="editingId === track.id">
						<UInput v-model="editingName" class="flex-1" autofocus @keyup.enter="saveRename(track)" @keyup.escape="cancelRename" />
						<UButton
							icon="lucide:check"
							color="primary"
							variant="ghost"
							:disabled="editingName.trim() === ''"
							:aria-label="$t('gallery.album.tracks.rename_save')"
							@click="saveRename(track)"
						/>
						<UButton
							icon="lucide:x"
							color="neutral"
							variant="ghost"
							:aria-label="$t('gallery.album.tracks.rename_cancel')"
							@click="cancelRename"
						/>
					</template>
					<template v-else>
						<span class="flex-1 truncate">{{ track.name }}</span>
						<UButton icon="lucide:pencil" color="neutral" variant="ghost" @click="startRename(track)" />
						<UButton icon="lucide:trash" color="error" variant="ghost" @click="askDelete(track)" />
					</template>
				</li>
			</ul>
		</div>
	</Fieldset>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
import TrackService from "@/v8/services/track-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useConfirmDialog } from "@/v8/composables/useConfirmDialog";
import { useAlbumStore } from "@/stores/AlbumState";

defineProps<{
	legendIcon: string;
	legendLabel: string;
}>();

const toast = useAppToast();
const { confirm } = useConfirmDialog();
const albumStore = useAlbumStore();

const tracks = computed<App.Http.Resources.Models.TrackResource[]>(() => albumStore.modelAlbum?.tracks ?? []);

const fileInput = ref<HTMLInputElement | null>(null);
const uploading = ref(false);

function triggerFileInput() {
	fileInput.value?.click();
}

function onFilesSelected(event: Event) {
	const input = event.target as HTMLInputElement;
	const files = input.files ? Array.from(input.files) : [];
	input.value = "";
	if (files.length === 0 || albumStore.album === undefined || albumStore.modelAlbum === undefined) {
		return;
	}

	uploading.value = true;
	TrackService.uploadTracks(albumStore.album.id, files)
		.then((response) => {
			if (albumStore.modelAlbum !== undefined) {
				albumStore.modelAlbum.tracks = response.data as App.Http.Resources.Models.TrackResource[];
			}
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("gallery.album.tracks.uploaded"), life: 3000 });
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("gallery.album.tracks.upload_failed"), life: 3000 });
		})
		.finally(() => {
			uploading.value = false;
		});
}

const editingId = ref<number | undefined>(undefined);
const editingName = ref("");

function startRename(track: App.Http.Resources.Models.TrackResource) {
	editingId.value = track.id;
	editingName.value = track.name;
}

function cancelRename() {
	editingId.value = undefined;
	editingName.value = "";
}

function saveRename(track: App.Http.Resources.Models.TrackResource) {
	const name = editingName.value.trim();
	if (name === "" || albumStore.album === undefined) {
		return;
	}

	TrackService.renameTrack(albumStore.album.id, track.id, name).then(() => {
		track.name = name;
		cancelRename();
		toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("gallery.album.tracks.renamed"), life: 3000 });
	});
}

function askDelete(track: App.Http.Resources.Models.TrackResource) {
	confirm({
		title: trans("gallery.album.tracks.delete_confirm_header"),
		message: trans("gallery.album.tracks.delete_confirm_message", { name: track.name }),
		severity: "danger",
	}).then((accepted) => {
		if (!accepted || albumStore.album === undefined || albumStore.modelAlbum === undefined) {
			return;
		}

		TrackService.deleteTrack(albumStore.album.id, track.id).then(() => {
			if (albumStore.modelAlbum !== undefined) {
				albumStore.modelAlbum.tracks = albumStore.modelAlbum.tracks.filter((t) => t.id !== track.id);
			}
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("gallery.album.tracks.deleted"), life: 3000 });
		});
	});
}
</script>
