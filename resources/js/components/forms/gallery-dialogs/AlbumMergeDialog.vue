<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div v-if="titleMovedTo !== undefined">
				<p class="p-9 text-center text-muted-color">{{ confirmation }}</p>
				<div class="flex">
					<Button severity="secondary" class="font-bold w-full border-none rounded-none rounded-bl-xl" @click="close">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button severity="contrast" class="font-bold w-full border-none rounded-none rounded-br-xl" @click="execute">
						{{ $t("dialogs.merge.merge") }}
					</Button>
				</div>
			</div>
			<div v-else>
				<div class="p-9" v-if="error_no_target === false">
					<span v-if="props.album" class="font-bold">
						{{ sprintf($t("dialogs.merge.merge_to"), props.album.title) }}
					</span>
					<span v-else class="font-bold">
						{{ sprintf($t("dialogs.merge.merge_to_multiple"), props.albumIds?.length) }}
					</span>
					<SearchTargetAlbum :album-ids="albumIds" @selected="selected" @no-target="error_no_target = true" />
				</div>
				<div v-else class="p-9">
					<p class="text-center text-muted-color">{{ $t("dialogs.merge.no_albums") }}</p>
				</div>
				<Button class="w-full font-bold rounded-none rounded-bl-xl rounded-br-xl border-none" severity="secondary" @click="closeCallback">
					{{ $t("dialogs.button.cancel") }}
				</Button>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import Button from "primevue/button";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import SearchTargetAlbum from "@/components/forms/album/SearchTargetAlbum.vue";
import AlbumService from "@/services/album-service";
import { useToast } from "primevue/usetoast";
import Dialog from "primevue/dialog";

const props = defineProps<{
	parentId: string | undefined;
	album?: App.Http.Resources.Models.ThumbAlbumResource;
	albumIds: string[];
}>();

const visible = defineModel<boolean>("visible", { default: false });

const emits = defineEmits<{
	merged: [];
}>();

const toast = useToast();
const titleMovedTo = ref<string | undefined>(undefined);
const destination_id = ref<string | undefined | null>(undefined);
const error_no_target = ref(false);

const confirmation = computed(() => {
	if (props.album) {
		return sprintf(trans("dialog.merge.confirm"), props.album.title, titleMovedTo.value);
	}
	return sprintf(trans("dialog.merge.confirm_multiple"), titleMovedTo.value);
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
			summary: sprintf(trans("dialog.merge.merged"), titleMovedTo.value),
			life: 3000,
		});
		AlbumService.clearCache(destination_id.value);
		for (let id in albumMergedIds) {
			AlbumService.clearCache(id);
		}
		if (props.parentId === undefined) {
			AlbumService.clearAlbums();
		} else {
			AlbumService.clearCache(props.parentId);
		}
		emits("merged");
	});
}
</script>
