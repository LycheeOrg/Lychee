<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>

		{{ $t("gallery_sizes.title") }}

		<template v-if="canEdit" #right>
			<span v-if="isDirty" class="flex items-center gap-1 text-sm text-muted">
				<span class="inline-block size-2 rounded-full bg-primary" aria-hidden="true" />
				{{ $t("gallery_sizes.unsaved") }}
			</span>
			<UButton :label="$t('gallery_sizes.revert')" color="neutral" variant="ghost" :disabled="!isDirty || saving" @click="revert" />
			<UButton :label="$t('gallery_sizes.save')" color="primary" :loading="saving" :disabled="!isDirty || saving" @click="save" />
		</template>
	</UHeader>

	<div v-if="!canEdit" class="max-w-5xl mx-auto mt-8 px-4 text-center">
		<UIcon name="lucide:lock" class="text-5xl text-muted mb-4" />
		<p class="text-muted">{{ $t("gallery_sizes.not_allowed") }}</p>
	</div>

	<div v-else class="mx-auto mt-4 px-4 pb-10 flex flex-col xl:flex-row gap-6">
		<div class="w-full xl:w-96 shrink-0 flex flex-col gap-6">
			<GallerySizesControls
				v-model:layout="layout"
				:sizes="draft"
				:editable="editable"
				:disabled="loading || loadError"
				:toggleable="isNarrow"
				@set="set"
				@reset="resetKey"
			/>
			<Fieldset :legend="$t('gallery_sizes.section_placeholders')" :toggleable="isNarrow">
				<div class="flex flex-col gap-4">
					<div class="flex items-center justify-between gap-2">
						<label for="gallery-sizes-album-count" class="text-sm font-medium">{{ $t("gallery_sizes.album_count") }}</label>
						<UInputNumber
							id="gallery-sizes-album-count"
							v-model="albumCount"
							:min="ALBUM_COUNT_RANGE.min"
							:max="ALBUM_COUNT_RANGE.max"
							size="sm"
							class="w-28"
						/>
					</div>
					<div class="flex items-center justify-between gap-2">
						<label for="gallery-sizes-photo-count" class="text-sm font-medium">{{ $t("gallery_sizes.photo_count") }}</label>
						<UInputNumber
							id="gallery-sizes-photo-count"
							v-model="photoCount"
							:min="PHOTO_COUNT_RANGE.min"
							:max="PHOTO_COUNT_RANGE.max"
							size="sm"
							class="w-28"
						/>
					</div>
					<UButton
						:label="$t('gallery_sizes.shuffle')"
						icon="lucide:shuffle"
						color="neutral"
						variant="outline"
						class="self-start"
						@click="shuffle"
					/>
				</div>
			</Fieldset>
		</div>
		<div class="flex-1 min-w-0">
			<GallerySizesPreview v-model:preset="preset" :sizes="draft" :layout="layout" :placeholders="placeholders" />
		</div>
	</div>
</template>
<script setup lang="ts">
/**
 * Feature 110 (FR-110-04 … FR-110-10): admin page previewing the 9 size
 * settings on generated placeholders at simulated window widths, then saving
 * them. Admins only (`settings.can_edit`).
 */
import { computed, ref, watch } from "vue";
import { breakpointsTailwind, useBreakpoints } from "@vueuse/core";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
import GallerySizesControls from "@/v8/components/gallerySizes/GallerySizesControls.vue";
import GallerySizesPreview from "@/v8/components/gallerySizes/GallerySizesPreview.vue";
import { useGlobalRights } from "@/composables/useGlobalRights";
import { useGallerySizesDraft } from "@/v8/composables/gallerySizes/useGallerySizesDraft";
import { ALBUM_COUNT_RANGE, PHOTO_COUNT_RANGE, generatePlaceholders } from "@/v8/utils/galleryPreviewPlaceholders";
import type { PreviewWidthPreset } from "@/v8/utils/galleryPreviewGeometry";

const { rights } = useGlobalRights();
const canEdit = computed(() => rights.value?.settings.can_edit ?? false);

const { draft, editable, loading, loadError, saving, isDirty, load, set, revert, resetKey, save } = useGallerySizesDraft();

const isNarrow = useBreakpoints(breakpointsTailwind).smaller("xl");

const layout = ref<App.Enum.PhotoLayoutType>("justified");
const preset = ref<PreviewWidthPreset>("window");
const albumCount = ref<number>(ALBUM_COUNT_RANGE.default);
const photoCount = ref<number>(PHOTO_COUNT_RANGE.default);
const seed = ref(1);

const placeholders = computed(() =>
	generatePlaceholders(seed.value, albumCount.value ?? ALBUM_COUNT_RANGE.min, photoCount.value ?? PHOTO_COUNT_RANGE.min),
);

function shuffle(): void {
	seed.value = Math.floor(Math.random() * 2 ** 31);
}

// Rights may arrive after mount; load the settings once they allow it.
watch(
	canEdit,
	(allowed) => {
		if (allowed) {
			load();
		}
	},
	{ immediate: true },
);
</script>
