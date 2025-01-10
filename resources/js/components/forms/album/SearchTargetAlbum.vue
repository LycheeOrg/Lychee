<template>
	<Select
		id="targetAlbum"
		class="w-full border-none"
		v-model="selectedTarget"
		@update:modelValue="selected"
		filter
		:placeholder="$t('dialogs.target_album.placeholder')"
		:loading="options === undefined"
		:options="options"
		optionLabel="original"
		showClear
	>
		<template #value="slotProps">
			<div v-if="slotProps.value" class="flex items-center">
				<div>{{ $t(slotProps.value.original) }}</div>
			</div>
		</template>
		<template #option="slotProps">
			<div class="flex items-center">
				<img :src="slotProps.option.thumb" alt="poster" class="w-4 rounded-sm" />
				<span class="ml-4 text-left">{{ slotProps.option.short_title }}</span>
			</div>
		</template>
	</Select>
</template>
<script setup lang="ts">
import { ref, watch } from "vue";
import AlbumService from "@/services/album-service";
import Select from "primevue/select";

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
