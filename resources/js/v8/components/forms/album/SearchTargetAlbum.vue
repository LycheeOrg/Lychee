<template>
	<USelectMenu
		id="targetAlbum"
		v-model="selectedTarget"
		class="w-full"
		:placeholder="$t('dialogs.target_album.placeholder')"
		:loading="options === undefined"
		:items="options"
		label-key="original"
		@update:model-value="selected"
	>
		<template #item-leading="{ item }">
			<img :src="item.thumb" alt="poster" class="w-4 rounded-sm" />
		</template>
	</USelectMenu>
</template>
<script setup lang="ts">
import { ref, watch } from "vue";
import AlbumService from "@/services/album-service";

const props = defineProps<{
	albumIds: string[] | undefined;
}>();

const albumIds = ref<string[] | null>(props.albumIds ?? null);
const emits = defineEmits<{
	selected: [target: App.Http.Resources.Models.TargetAlbumResource];
	"no-target": [];
}>();

const options = ref<App.Http.Resources.Models.TargetAlbumResource[] | undefined>(undefined);
const selectedTarget = ref<App.Http.Resources.Models.TargetAlbumResource | undefined>(undefined);

function load() {
	AlbumService.getTargetListAlbums(albumIds.value).then((response) => {
		options.value = response.data;
		if (options.value.length === 0) {
			emits("no-target");
		}
	});
}

load();

function selected() {
	if (selectedTarget.value === undefined) {
		return;
	}

	emits("selected", selectedTarget.value);
}

watch(
	() => props.albumIds,
	(newAlbumId, _oldAlbumId) => {
		albumIds.value = newAlbumId ?? null;
		load();
	},
);
</script>
