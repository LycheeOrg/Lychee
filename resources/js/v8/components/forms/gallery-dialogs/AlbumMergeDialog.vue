<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<div v-if="titleMovedTo !== undefined">
				<p class="text-center text-muted">{{ confirmation }}</p>
			</div>
			<div v-else>
				<div v-if="error_no_target === false">
					<span v-if="props.album" class="font-bold">
						{{ sprintf($t("dialogs.merge.merge_to"), props.album.title) }}
					</span>
					<span v-else class="font-bold">
						{{ sprintf($t("dialogs.merge.merge_to_multiple"), props.albumIds?.length) }}
					</span>
					<SearchTargetAlbum :album-ids="albumIds" @selected="selected" @no-target="error_no_target = true" />
				</div>
				<div v-else>
					<p class="text-center text-muted">{{ $t("dialogs.merge.no_albums") }}</p>
				</div>
			</div>
		</template>
		<template #footer>
			<div v-if="titleMovedTo !== undefined" class="flex w-full gap-2">
				<UButton color="neutral" variant="soft" class="flex-1 justify-center font-bold" @click="close">
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="neutral" class="flex-1 justify-center font-bold" @click="execute">
					{{ $t("dialogs.merge.merge") }}
				</UButton>
			</div>
			<UButton
				v-else
				color="neutral"
				variant="soft"
				class="w-full justify-center font-bold"
				@click="
					() => {
						visible = false;
					}
				"
			>
				{{ $t("dialogs.button.cancel") }}
			</UButton>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import SearchTargetAlbum from "@/v8/components/forms/album/SearchTargetAlbum.vue";
import AlbumService from "@/services/album-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useRouter } from "vue-router";
import { usePhotoRoute } from "@/composables/photo/photoRoute";

const props = defineProps<{
	album?: App.Http.Resources.Models.ThumbAlbumResource;
	albumIds: string[];
}>();

const router = useRouter();
const { getParentId } = usePhotoRoute(router);
const visible = defineModel<boolean>("open", { default: false });

const emits = defineEmits<{
	merged: [];
}>();

const toast = useAppToast();
const titleMovedTo = ref<string | undefined>(undefined);
const destination_id = ref<string | undefined | null>(undefined);
const error_no_target = ref(false);

const confirmation = computed(() => {
	if (props.album) {
		return sprintf(trans("dialogs.merge.confirm"), props.album.title, titleMovedTo.value);
	}
	return sprintf(trans("dialogs.merge.confirm_multiple"), titleMovedTo.value);
});

function selected(target: App.Http.Resources.Models.TargetAlbumResource) {
	titleMovedTo.value = target.original;
	destination_id.value = target.id;
}

function close() {
	titleMovedTo.value = undefined;
	destination_id.value = undefined;
	visible.value = false;
}

function execute() {
	if (destination_id.value === undefined) {
		return;
	}
	visible.value = false;
	let albumMergedIds = [];
	if (props.album) {
		albumMergedIds.push(props.album.id);
	} else {
		albumMergedIds = props.albumIds as string[];
	}

	AlbumService.move(destination_id.value, albumMergedIds).then(() => {
		AlbumService.clearCache(destination_id.value);
		toast.add({
			severity: "success",
			summary: sprintf(trans("dialogs.merge.merged"), titleMovedTo.value),
			life: 3000,
		});
		AlbumService.clearCache(destination_id.value);
		for (const id in albumMergedIds) {
			AlbumService.clearCache(id);
		}
		if (getParentId() === undefined) {
			AlbumService.clearAlbums();
		} else {
			AlbumService.clearCache(getParentId());
		}

		// RESET !
		destination_id.value = undefined;
		titleMovedTo.value = undefined;

		emits("merged");
	});
}
</script>
