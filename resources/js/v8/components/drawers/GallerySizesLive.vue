<template>
	<USlideover
		v-model:open="is_gallery_sizes_live_open"
		:modal="false"
		:overlay="false"
		:dismissible="false"
		side="right"
		class="w-sm"
		:title="$t('gallery_sizes.live_title')"
		:description="$t('gallery_sizes.live_hint')"
	>
		<template #body>
			<p v-if="!sections.albums && !sections.photos" class="text-sm text-muted">{{ $t("gallery_sizes.live_empty") }}</p>
			<GallerySizesControls
				v-else
				plain
				:sizes="draft"
				:editable="editable"
				:layout="shownLayout"
				:disabled="loading || loadError"
				:show-albums="sections.albums"
				:show-photos="sections.photos"
				@set="set"
				@reset="resetKey"
				@update:layout="showLayout"
			/>
		</template>
		<template #footer>
			<div class="flex w-full items-center justify-between gap-2">
				<span v-if="isDirty" class="flex items-center gap-1 text-sm text-muted">
					<span class="inline-block size-2 rounded-full bg-primary" aria-hidden="true" />
					{{ $t("gallery_sizes.unsaved") }}
				</span>
				<span v-else />
				<div class="flex gap-2">
					<UButton :label="$t('gallery_sizes.revert')" color="neutral" variant="ghost" :disabled="!isDirty || saving" @click="revert" />
					<UButton :label="$t('gallery_sizes.save')" color="primary" :loading="saving" :disabled="!isDirty || saving" @click="save" />
				</div>
			</div>
		</template>
	</USlideover>
</template>
<script setup lang="ts">
/**
 * Feature 110 (FR-110-14, FR-110-15): the size controls over the real
 * gallery. While open, the draft is the gallery's live override
 * (`useGallerySizesLiveStore`), so grids and drag selection re-lay out at
 * once. Closing discards unsaved values and restores the photo layout shown
 * before opening. Opened from Spotlight only (FR-110-13).
 */
import { computed, watch } from "vue";
import { storeToRefs } from "pinia";
import { useRoute } from "vue-router";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useLayoutStore } from "@/stores/LayoutState";
import GallerySizesControls from "@/v8/components/gallerySizes/GallerySizesControls.vue";
import { useGallerySizesDraft } from "@/v8/composables/gallerySizes/useGallerySizesDraft";
import { LIVE_GALLERY_ROUTES, useGallerySizesLiveStore, useLiveGallerySections } from "@/v8/composables/gallerySizes/gallerySizesLive";

const { is_gallery_sizes_live_open } = storeToRefs(useTogglablesStateStore());
const layoutStore = useLayoutStore();
const live = useGallerySizesLiveStore();
const route = useRoute();

const { draft, editable, loading, loadError, saving, isDirty, load, set, revert, resetKey, resetToSession, save } = useGallerySizesDraft();

// Only the sections whose tiles are on this page (FR-110-14).
const sections = useLiveGallerySections(() => route.name);

// The controls only know the four photo layouts; the list view keeps its own rows.
const shownLayout = computed<App.Enum.PhotoLayoutType>(() => (layoutStore.layout === "list" ? "justified" : layoutStore.layout));

function showLayout(layout: App.Enum.PhotoLayoutType): void {
	layoutStore.layout = layout;
}

// The override starts once the stored values are in: until then the controls are
// disabled and the gallery keeps reading its stores, so opening never moves a tile.
function open(): void {
	live.start(layoutStore.layout);
	resetToSession();
	load().then(() => {
		if (is_gallery_sizes_live_open.value && !loadError.value) {
			live.apply(draft.value);
		}
	});
}

function close(): void {
	const layoutBefore = live.stop();
	if (layoutBefore !== null) {
		layoutStore.layout = layoutBefore;
	}
}

watch(is_gallery_sizes_live_open, (isOpen) => (isOpen ? open() : close()));

// Each edit replaces the draft object, so a shallow watch sees every change.
watch(draft, (sizes) => {
	if (is_gallery_sizes_live_open.value && !loading.value) {
		live.apply(sizes);
	}
});

// Leaving the gallery routes closes the drawer and discards (FR-110-15).
watch(
	() => route.name,
	(name) => {
		if (is_gallery_sizes_live_open.value && !LIVE_GALLERY_ROUTES.includes(String(name))) {
			is_gallery_sizes_live_open.value = false;
		}
	},
);
</script>
