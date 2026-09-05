<template>
	<img :src="src" :alt="$t('gallery.thumbnail')" />
</template>
<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue";
import ThumbAssetService from "@/services/thumb-asset-service";
import { useImageHelpers } from "@/utils/Helpers";

const props = defineProps<{
	albumId: string;
	photoId: string | null;
	type?: App.Enum.SizeVariantAssetType;
}>();

const { getNoImageIcon } = useImageHelpers();
const placeholder = getNoImageIcon();

const src = ref<string>(placeholder);

// When `type` isn't pinned by the caller, pick the 2x variant on HiDPI screens instead of
// always fetching the 1x one - devicePixelRatio doesn't change during a session, so this is
// only read once rather than tracked reactively.
const effectiveType = computed<App.Enum.SizeVariantAssetType>(() => props.type ?? (window.devicePixelRatio > 1 ? "small2x" : "small"));

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
	const acquired = ThumbAssetService.acquire(props.albumId, props.photoId, effectiveType.value);
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

watch([() => props.albumId, () => props.photoId, effectiveType], load);

onBeforeUnmount(() => {
	releaseCurrent();
});
</script>
