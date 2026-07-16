<template>
	<div class="w-full p-4 pt-0">
		<!-- Panel header -->
		<div class="flex items-center justify-between mb-4">
			<span class="font-semibold text-sm text-muted uppercase tracking-wide">{{ $t("gallery.search.advanced.title") }}</span>
			<UButton :label="$t('gallery.search.advanced.clear')" color="neutral" variant="ghost" size="sm" icon="lucide:x" @click="onClear" />
		</div>

		<!-- Row 1: Title / Description / Location -->
		<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
			<UFormField :label="$t('gallery.search.advanced.title_label')">
				<UInput v-model="title" class="w-full" @update:model-value="onFieldChange" />
			</UFormField>
			<UFormField :label="$t('gallery.search.advanced.description')">
				<UInput v-model="description" class="w-full" @update:model-value="onFieldChange" />
			</UFormField>
			<UFormField :label="$t('gallery.search.advanced.location')">
				<UInput v-model="location" class="w-full" @update:model-value="onFieldChange" />
			</UFormField>
		</div>

		<!-- Row 2: Tags / Date from / Date to -->
		<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
			<UFormField :label="$t('gallery.search.advanced.tags')">
				<UInput
					v-model="tags"
					class="w-full"
					:placeholder="$t('gallery.search.advanced.tags_placeholder')"
					@update:model-value="onFieldChange"
				/>
			</UFormField>
			<UFormField :label="$t('gallery.search.advanced.date_from')">
				<input
					v-model="dateFromInput"
					type="date"
					class="w-full border border-default rounded-md px-2 py-1.5 bg-default text-sm"
					@change="onFieldChange"
				/>
			</UFormField>
			<UFormField :label="$t('gallery.search.advanced.date_to')">
				<input
					v-model="dateToInput"
					type="date"
					class="w-full border border-default rounded-md px-2 py-1.5 bg-default text-sm"
					@change="onFieldChange"
				/>
			</UFormField>
		</div>

		<!-- Row 3: Type / Orientation / Rating avg / Rating own -->
		<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
			<div class="flex flex-col gap-1">
				<label class="text-xs font-medium text-muted">{{ $t("gallery.search.advanced.type") }}</label>
				<div class="flex items-center gap-2">
					<UTooltip :text="$t('gallery.search.advanced.type_image')">
						<button
							class="hover:text-highlighted cursor-pointer"
							:class="{
								'text-muted': type !== 'image',
								'text-primary': type === 'image',
							}"
							@click="updateType('image')"
						>
							<UIcon name="lucide:image" />
						</button>
					</UTooltip>
					<UTooltip :text="$t('gallery.search.advanced.type_video')">
						<button
							class="hover:text-highlighted cursor-pointer"
							:class="{
								'text-muted': type !== 'video',
								'text-primary': type === 'video',
							}"
							@click="updateType('video')"
						>
							<UIcon name="lucide:video" />
						</button>
					</UTooltip>
					<UTooltip :text="$t('gallery.search.advanced.type_raw')">
						<button
							class="hover:text-highlighted cursor-pointer"
							:class="{
								'text-muted': type !== 'raw',
								'text-primary': type === 'raw',
							}"
							@click="updateType('raw')"
						>
							<UIcon name="lucide:file" />
						</button>
					</UTooltip>
					<UTooltip :text="$t('gallery.search.advanced.type_live')">
						<button
							class="hover:text-highlighted cursor-pointer"
							:class="{
								'text-muted': type !== 'live',
								'text-primary': type === 'live',
							}"
							@click="updateType('live')"
						>
							<UIcon name="lucide:smartphone" />
						</button>
					</UTooltip>
				</div>
			</div>
			<div class="flex flex-col gap-1">
				<label class="text-xs font-medium text-muted">{{ $t("gallery.search.advanced.orientation") }}</label>
				<div class="flex items-center gap-2">
					<UTooltip :text="$t('gallery.search.advanced.orientation_landscape')">
						<button
							class="block mt-2 h-4 w-6 border hover:border-highlighted transition duration-100 cursor-pointer rounded-sm"
							:class="{
								'border-(--ui-text-muted)': orientation !== 'landscape',
								'border-primary': orientation === 'landscape',
							}"
							@click="updateOrientation('landscape')"
						/>
					</UTooltip>
					<UTooltip :text="$t('gallery.search.advanced.orientation_portrait')">
						<button
							class="block h-6 w-4 border hover:border-highlighted transition duration-100 cursor-pointer rounded-sm"
							:class="{
								'border-(--ui-text-muted)': orientation !== 'portrait',
								'border-primary': orientation === 'portrait',
							}"
							@click="updateOrientation('portrait')"
						/>
					</UTooltip>
					<UTooltip :text="$t('gallery.search.advanced.orientation_square')">
						<button
							class="block mt-2 h-4 w-4 border hover:border-highlighted transition duration-100 cursor-pointer rounded-sm"
							:class="{
								'border-(--ui-text-muted)': orientation !== 'square',
								'border-primary': orientation === 'square',
							}"
							@click="updateOrientation('square')"
						/>
					</UTooltip>
				</div>
			</div>
			<div class="flex flex-col gap-1">
				<label class="text-xs font-medium text-muted">{{ $t("gallery.search.advanced.rating_min") }}</label>
				<rating :loading="false" :selected-rating="ratingMin" :handle-rating-click="onMinRating" />
			</div>
			<div v-if="userStore.isLoggedIn" class="flex flex-col gap-1">
				<label class="text-xs font-medium text-muted">{{ $t("gallery.search.advanced.rating_own") }}</label>
				<rating :loading="false" :selected-rating="ratingOwn" :handle-rating-click="onOwnRating" />
			</div>
		</div>

		<!-- EXIF sub-section -->
		<div class="border-t border-default pt-3">
			<span class="text-xs font-semibold text-muted uppercase tracking-wide mb-3 block">{{ $t("gallery.search.advanced.exif") }}</span>
			<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
				<UFormField :label="$t('gallery.search.advanced.make')">
					<UInput v-model="make" class="w-full" @update:model-value="onFieldChange" />
				</UFormField>
				<UFormField :label="$t('gallery.search.advanced.model')">
					<UInput v-model="cameraModel" class="w-full" @update:model-value="onFieldChange" />
				</UFormField>
				<UFormField :label="$t('gallery.search.advanced.lens')">
					<UInput v-model="lens" class="w-full" @update:model-value="onFieldChange" />
				</UFormField>
				<UFormField :label="$t('gallery.search.advanced.aperture')">
					<UInput v-model="aperture" class="w-full" @update:model-value="onFieldChange" />
				</UFormField>
			</div>
			<div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
				<UFormField :label="$t('gallery.search.advanced.shutter')">
					<UInput v-model="shutter" class="w-full" @update:model-value="onFieldChange" />
				</UFormField>
				<UFormField :label="$t('gallery.search.advanced.focal')">
					<UInput v-model="focal" class="w-full" @update:model-value="onFieldChange" />
				</UFormField>
				<UFormField :label="$t('gallery.search.advanced.iso')">
					<UInput v-model="iso" class="w-full" @update:model-value="onFieldChange" />
				</UFormField>
			</div>
		</div>
	</div>
</template>
<script lang="ts" setup>
import { computed, ref } from "vue";
import { useUserStore } from "@/stores/UserState";
import { assembleTokens, parseTokens, type AdvancedSearchState } from "@/composables/useSearchTokenAssembler";
import rating from "@/v8/components/forms/basic/rating.vue";

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

const dateFromInput = computed<string>({
	get: () => formatDate(dateFrom.value),
	set: (v: string) => {
		dateFrom.value = v ? new Date(v + "T00:00:00") : null;
	},
});
const dateToInput = computed<string>({
	get: () => formatDate(dateTo.value),
	set: (v: string) => {
		dateTo.value = v ? new Date(v + "T00:00:00") : null;
	},
});

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
