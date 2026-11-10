<template>
	<img :src="src" :alt="$t('gallery.thumbnail')" @error="onError" />
</template>
<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue";
import ThumbAssetService from "@/services/thumb-asset-service";
import { isObjectUrl } from "@/services/asset-url";
import { useImageHelpers } from "@/utils/Helpers";
import { screenDensity } from "@/v8/utils/screenDensity";

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
// always fetching the 1x one - the same choice as the direct grid URLs (Feature 102).
const effectiveType = computed<App.Enum.SizeVariantAssetType>(() => props.type ?? (screenDensity() === 2 ? "small2x" : "small"));

let release: (() => void) | undefined;
let loadId = 0;
// The URL `/Asset` gave for the current load, and whether it was already asked again (FR-113-03).
let assetUrl: string | undefined;
let assetRetried = false;

function releaseCurrent() {
	if (release !== undefined) {
		release();
		release = undefined;
	}
}

function load() {
	releaseCurrent();
	const id = ++loadId;
	assetUrl = undefined;
	assetRetried = false;

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
		.then((url) => {
			if (id === loadId) {
				assetUrl = url;
				src.value = url;
			}
		})
		.catch(() => {
			if (id === loadId) {
				src.value = placeholder;
			}
		});
}

type ErrorAction = "none" | "fallback" | "retry" | "give-up";

// An S3 URL from `/Asset` is asked for once more, then gives up (FR-113-03); an object URL holds the
// bytes already, asking again would not help. Checked first: on a public bucket `/Asset` hands back
// the very URL the direct one was. A direct URL that does not load (missing file, expired S3 link)
// falls back to `/Asset` (FR-102-06).
function errorAction(failed: string): ErrorAction {
	if (failed === assetUrl) {
		if (isObjectUrl(failed)) {
			return "none";
		}
		return assetRetried ? "give-up" : "retry";
	}
	if (typeof props.directUrl === "string" && failed === props.directUrl) {
		return "fallback";
	}
	return "none";
}

function onError() {
	if (props.photoId === null) {
		return;
	}
	const action = errorAction(src.value);
	if (action === "none") {
		return;
	}
	releaseCurrent();
	// Placeholder meanwhile, so a URL handed back unchanged loads (and fails) again instead of sticking.
	src.value = placeholder;
	if (action === "give-up") {
		return;
	}
	if (action === "retry") {
		assetRetried = true;
		ThumbAssetService.invalidate(props.albumId, props.photoId, effectiveType.value);
	}
	loadAsset(props.photoId, ++loadId);
}

load();

watch([() => props.albumId, () => props.photoId, effectiveType, () => props.directUrl, () => props.pending], load);

onBeforeUnmount(() => {
	releaseCurrent();
});
</script>
