<template>
	<div class="w-full border border-surface-200 dark:border-surface-700 rounded-lg bg-surface-50 dark:bg-surface-900 p-4 mb-2">
		<!-- Panel header -->
		<div class="flex items-center justify-between mb-4">
			<span class="font-semibold text-sm text-muted-color uppercase tracking-wide">{{ $t("gallery.search.advanced.title") }}</span>
			<Button :label="$t('gallery.search.advanced.clear')" severity="secondary" size="small" text icon="pi pi-times" @click="onClear" />
		</div>

		<!-- Row 1: Title / Description / Location -->
		<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
			<FloatLabel variant="on">
				<InputText v-model="title" @update:model-value="onFieldChange" />
				<label class="font-medium text-muted-color">{{ $t("gallery.search.advanced.title_label") }}</label>
			</FloatLabel>
			<FloatLabel variant="on">
				<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.description") }}</label>
				<InputText v-model="description" @update:model-value="onFieldChange" />
			</FloatLabel>
			<FloatLabel variant="on">
				<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.location") }}</label>
				<InputText v-model="location" @update:model-value="onFieldChange" />
			</FloatLabel>
		</div>

		<!-- Row 2: Tags / Date from / Date to -->
		<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
			<FloatLabel variant="on">
				<InputText v-model="tags" :placeholder="$t('gallery.search.advanced.tags_placeholder')" @update:model-value="onFieldChange" />
				<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.tags") }}</label>
			</FloatLabel>
			<FloatLabel variant="on">
				<DatePicker
					v-model="dateFrom"
					date-format="yy-mm-dd"
					update-model-type="date"
					:show-time="false"
					:manual-input="true"
					@update:model-value="onFieldChange"
				/>
				<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.date_from") }}</label>
			</FloatLabel>
			<FloatLabel variant="on">
				<DatePicker
					v-model="dateTo"
					date-format="yy-mm-dd"
					update-model-type="date"
					:show-time="false"
					:manual-input="true"
					@update:model-value="onFieldChange"
				/>
				<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.date_to") }}</label>
			</FloatLabel>
		</div>

		<!-- Row 3: Type / Orientation / Rating avg / Rating own -->
		<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
			<div class="flex flex-col gap-1">
				<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.type") }}</label>
				<div class="flex items-center gap-2">
					<button
						class="hover:text-muted-color-emphasis cursor-pointer"
						:class="{
							'text-muted-color': type !== 'image',
							'text-primary-500': type === 'image',
						}"
						v-tooltip.bottom="$t('gallery.search.advanced.type_image')"
						@click="updateType('image')"
					>
						<i class="pi pi-image" />
					</button>
					<button
						class="hover:text-muted-color-emphasis cursor-pointer"
						:class="{
							'text-muted-color': type !== 'video',
							'text-primary-500': type === 'video',
						}"
						v-tooltip.bottom="$t('gallery.search.advanced.type_video')"
						@click="updateType('video')"
					>
						<i class="pi pi-video" />
					</button>
					<button
						class="hover:text-muted-color-emphasis cursor-pointer"
						:class="{
							'text-muted-color': type !== 'raw',
							'text-primary-500': type === 'raw',
						}"
						v-tooltip.bottom="$t('gallery.search.advanced.type_raw')"
						@click="updateType('raw')"
					>
						<i class="pi pi-file" />
					</button>
					<button
						class="hover:text-muted-color-emphasis cursor-pointer"
						:class="{
							'text-muted-color': type !== 'live',
							'text-primary-500': type === 'live',
						}"
						v-tooltip.bottom="$t('gallery.search.advanced.type_live')"
						@click="updateType('live')"
					>
						<i class="pi pi-mobile" />
					</button>
				</div>
			</div>
			<div class="flex flex-col gap-1">
				<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.orientation") }}</label>
				<div class="flex items-center gap-2">
					<button
						class="block mt-2 h-4 w-6 border hover:border-surface-800 dark:hover:border-surface-200 transition duration-100 cursor-pointer rounded-sm"
						:class="{
							'border-surface-400 dark:border-surface-400': orientation !== 'landscape',
							'border-primary-400': orientation === 'landscape',
						}"
						@click="updateOrientation('landscape')"
						v-tooltip.bottom="$t('gallery.search.advanced.orientation_landscape')"
					/>
					<button
						class="block h-6 w-4 border hover:border-surface-800 dark:hover:border-surface-200 transition duration-100 cursor-pointer rounded-sm"
						:class="{
							'border-surface-400 dark:border-surface-400': orientation !== 'portrait',
							'border-primary-400': orientation === 'portrait',
						}"
						@click="updateOrientation('portrait')"
						v-tooltip.bottom="$t('gallery.search.advanced.orientation_portrait')"
					/>
					<button
						class="block mt-2 h-4 w-4 border hover:border-surface-800 dark:hover:border-surface-200 transition duration-100 cursor-pointer rounded-sm"
						:class="{
							'border-surface-400 dark:border-surface-400': orientation !== 'square',
							'border-primary-400': orientation === 'square',
						}"
						@click="updateOrientation('square')"
						v-tooltip.bottom="$t('gallery.search.advanced.orientation_square')"
					/>
				</div>
			</div>
			<div class="flex flex-col gap-1">
				<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.rating_min") }}</label>
				<Rating :loading="false" :selectedRating="ratingMin" :handleRatingClick="onMinRating" />
			</div>
			<div v-if="userStore.isLoggedIn" class="flex flex-col gap-1">
				<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.rating_own") }}</label>
				<Rating :loading="false" :selectedRating="ratingOwn" :handleRatingClick="onOwnRating" />
			</div>
		</div>

		<!-- EXIF sub-section -->
		<div class="border-t border-surface-200 dark:border-surface-700 pt-3">
			<span class="text-xs font-semibold text-muted-color uppercase tracking-wide mb-3 block">{{ $t("gallery.search.advanced.exif") }}</span>
			<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
				<FloatLabel variant="on">
					<InputText v-model="make" @update:model-value="onFieldChange" />
					<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.make") }}</label>
				</FloatLabel>
				<FloatLabel variant="on">
					<InputText v-model="cameraModel" @update:model-value="onFieldChange" />
					<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.model") }}</label>
				</FloatLabel>
				<FloatLabel variant="on">
					<InputText v-model="lens" @update:model-value="onFieldChange" />
					<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.lens") }}</label>
				</FloatLabel>
				<FloatLabel variant="on">
					<InputText v-model="aperture" @update:model-value="onFieldChange" />
					<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.aperture") }}</label>
				</FloatLabel>
			</div>
			<div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
				<FloatLabel variant="on">
					<InputText v-model="shutter" @update:model-value="onFieldChange" />
					<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.shutter") }}</label>
				</FloatLabel>
				<FloatLabel variant="on">
					<InputText v-model="focal" @update:model-value="onFieldChange" />
					<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.focal") }}</label>
				</FloatLabel>
				<FloatLabel variant="on">
					<InputText v-model="iso" @update:model-value="onFieldChange" />
					<label class="text-xs font-medium text-muted-color">{{ $t("gallery.search.advanced.iso") }}</label>
				</FloatLabel>
			</div>
		</div>
	</div>
</template>
<script lang="ts" setup>
import { ref } from "vue";
import DatePicker from "primevue/datepicker";
import Button from "primevue/button";
import { useUserStore } from "@/stores/UserState";
import { assembleTokens, parseTokens, type AdvancedSearchState } from "@/composables/useSearchTokenAssembler";
import InputText from "@/components/forms/basic/InputText.vue";
import FloatLabel from "primevue/floatlabel";
import Rating from "@/components/forms/basic/rating.vue";

const userStore = useUserStore();

const emits = defineEmits<{
	"update:tokens": [tokens: string];
	clear: [];
}>();

// ---------------------------------------------------------------------------
// Field state
// ---------------------------------------------------------------------------
const title = ref("");
const description = ref("");
const location = ref("");
const tags = ref("");
const dateFrom = ref<Date | null>(null);
const dateTo = ref<Date | null>(null);
const type = ref("");
const orientation = ref("");
const ratingMin = ref<number | undefined>(undefined);
const ratingOwn = ref<number | undefined>(undefined);
const make = ref("");
const cameraModel = ref(""); // named 'cameraModel' to avoid collision with 'model' keyword
const lens = ref("");
const aperture = ref("");
const shutter = ref("");
const focal = ref("");
const iso = ref("");

function onMinRating(value: 1 | 2 | 3 | 4 | 5) {
	ratingMin.value = value === ratingMin.value ? undefined : value;
	onFieldChange();
}

function onOwnRating(value: 1 | 2 | 3 | 4 | 5) {
	ratingOwn.value = value === ratingOwn.value ? undefined : value;
	onFieldChange();
}

function updateOrientation(value: string) {
	orientation.value = value === orientation.value ? "" : value;
	onFieldChange();
}

function updateType(value: string) {
	type.value = value === type.value ? "" : value;
	onFieldChange();
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------
function formatDate(d: Date | null): string {
	if (!d) return "";
	const y = d.getFullYear();
	const m = String(d.getMonth() + 1).padStart(2, "0");
	const day = String(d.getDate()).padStart(2, "0");
	return `${y}-${m}-${day}`;
}

function toAssemblerState(): AdvancedSearchState {
	return {
		title: title.value,
		description: description.value,
		location: location.value,
		tags: tags.value,
		dateFrom: formatDate(dateFrom.value),
		dateTo: formatDate(dateTo.value),
		type: type.value,
		orientation: orientation.value,
		ratingMin: ratingMin.value ? String(ratingMin.value) : "",
		ratingOwn: ratingOwn.value ? String(ratingOwn.value) : "",
		make: make.value,
		model: cameraModel.value,
		lens: lens.value,
		aperture: aperture.value,
		shutter: shutter.value,
		focal: focal.value,
		iso: iso.value,
	};
}

// ---------------------------------------------------------------------------
// Event handlers
// ---------------------------------------------------------------------------
function onFieldChange() {
	emits("update:tokens", assembleTokens(toAssemblerState(), userStore.isLoggedIn));
}

function onClear() {
	title.value = "";
	description.value = "";
	location.value = "";
	tags.value = "";
	dateFrom.value = null;
	dateTo.value = null;
	type.value = "";
	orientation.value = "";
	ratingMin.value = undefined;
	ratingOwn.value = undefined;
	make.value = "";
	cameraModel.value = "";
	lens.value = "";
	aperture.value = "";
	shutter.value = "";
	focal.value = "";
	iso.value = "";
	emits("update:tokens", "");
	emits("clear");
}

// ---------------------------------------------------------------------------
// parseAndLoad – called by SearchBox to reflect raw input changes in the panel
// ---------------------------------------------------------------------------
function parseAndLoad(raw: string): void {
	const { advanced } = parseTokens(raw);
	title.value = advanced.title;
	description.value = advanced.description;
	location.value = advanced.location;
	tags.value = advanced.tags;
	dateFrom.value = advanced.dateFrom ? new Date(advanced.dateFrom + "T00:00:00") : null;
	dateTo.value = advanced.dateTo ? new Date(advanced.dateTo + "T00:00:00") : null;
	type.value = advanced.type;
	orientation.value = advanced.orientation;
	ratingMin.value = advanced.ratingMin ? parseInt(advanced.ratingMin) : undefined;
	ratingOwn.value = advanced.ratingOwn ? parseInt(advanced.ratingOwn) : undefined;
	make.value = advanced.make;
	cameraModel.value = advanced.model;
	lens.value = advanced.lens;
	aperture.value = advanced.aperture;
	shutter.value = advanced.shutter;
	focal.value = advanced.focal;
	iso.value = advanced.iso;
	// Intentionally NOT emitting 'update:tokens' here — the parent (SearchBox)
	// is the source of truth when calling parseAndLoad.
}

defineExpose({ parseAndLoad });
</script>
