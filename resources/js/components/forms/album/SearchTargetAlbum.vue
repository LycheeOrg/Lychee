<template>
	<Select
		id="targetAlbum"
		class="w-full border-none"
		v-model="selectedTarget"
		@update:modelValue="selected"
		filter
		placeholder="Select album"
		:loading="options.length === 0"
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
import { ref } from "vue";
import AlbumService from "@/services/album-service";
import Select from "primevue/select";

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource | null;
}>();

const emits = defineEmits(["selected"]);

const options = ref([] as App.Http.Resources.Models.TargetAlbumResource[]);
const selectedTarget = ref(undefined as App.Http.Resources.Models.TargetAlbumResource | undefined);

function load() {
	AlbumService.getTargetListAlbums(props.album?.id ?? null).then((response) => {
		options.value = response.data;
	});
}

load();

function selected() {
	emits("selected", selectedTarget.value);
}
</script>
