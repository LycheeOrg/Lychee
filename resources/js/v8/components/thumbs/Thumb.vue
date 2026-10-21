<template>
	<img :src="src" :alt="$t('gallery.thumbnail')" @error="onError" />
</template>
<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue";
import ThumbAssetService from "@/services/thumb-asset-service";
import { useImageHelpers } from "@/utils/Helpers";

const props = defineProps<{
	albumId: string;
	photoId: string | null;
	type?: App.Enum.SizeVariantAssetType;
	/**
	 * Feature 102 (FR-102-06): a direct URL is rendered as is, falling back
	 * to `/Asset` if it fails to load; `null`/absent loads through `/Asset`.
	 */
	directUrl?: string | null;
	/** The direct URL is still being asked for: show the placeholder only. */
	pending?: boolean;
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
	if (props.pending === true) {
		return;
	}
	if (typeof props.directUrl === "string") {
		src.value = props.directUrl;
		return;
	}
	loadAsset(props.photoId, id);
}

function loadAsset(photoId: string, id: number) {
	const acquired = ThumbAssetService.acquire(props.albumId, photoId, effectiveType.value);
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

// A direct URL that does not load (missing file, expired S3 link) falls back to `/Asset`.
function onError() {
	if (props.photoId !== null && typeof props.directUrl === "string" && src.value === props.directUrl) {
		releaseCurrent();
		loadAsset(props.photoId, ++loadId);
	}
}

load();

watch([() => props.albumId, () => props.photoId, effectiveType, () => props.directUrl, () => props.pending], load);

onBeforeUnmount(() => {
	releaseCurrent();
});
</script>
