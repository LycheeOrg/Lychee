<template>
	<img :src="src" :alt="$t('gallery.thumbnail')" />
</template>
<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from "vue";
import ThumbAssetService from "@/services/thumb-asset-service";
import { useImageHelpers } from "@/utils/Helpers";

const props = withDefaults(
	defineProps<{
		albumId: string;
		photoId: string | null;
		type?: App.Enum.SizeVariantAssetType;
	}>(),
	{
		type: "thumb",
	},
);

const { getNoImageIcon } = useImageHelpers();
const placeholder = getNoImageIcon();

const src = ref<string>(placeholder);

let release: (() => void) | undefined;
let loadId = 0;

function releaseCurrent() {
	if (release !== undefined) {
		release();
		release = undefined;
	}
}

function load() {
	releaseCurrent();
	const id = ++loadId;

	if (props.photoId === null) {
		src.value = placeholder;
		return;
	}

	src.value = placeholder;
	const acquired = ThumbAssetService.acquire(props.albumId, props.photoId, props.type);
	release = acquired.release;

	acquired.promise
		.then((objectUrl) => {
			if (id === loadId) {
				src.value = objectUrl;
			}
		})
		.catch(() => {
			if (id === loadId) {
				src.value = placeholder;
			}
		});
}

load();

watch([() => props.albumId, () => props.photoId, () => props.type], load);

onBeforeUnmount(() => {
	releaseCurrent();
});
</script>
