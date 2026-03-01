<template>
	<div
		v-if="albumStore.album && albumStore.album.preFormattedData.url"
		:class="['w-full', album_header_size === 'half_screen' ? 'h-1/2' : 'h-full', 'relative', album_header_size]"
	>
		<template v-if="!is_album_enhanced_display_enabled">
			<img class="absolute block top-0 left-0 w-full h-full object-cover object-center z-0" :src="albumStore.album.preFormattedData.url" />
			<div class="h-full ltr:pl-7 rtl:pr-7 pt-7 relative text-shadow-sm w-full bg-linear-to-b from-black/20 via-80%">
				<h1 class="font-bold text-4xl text-surface-0">{{ albumStore.album.title }}</h1>
				<span v-if="albumStore.album.preFormattedData.min_max_text" class="text-surface-200 text-sm">
					{{ albumStore.album.preFormattedData.min_max_text }}
				</span>
			</div>
		</template>
		<template v-if="is_album_enhanced_display_enabled">
			<AlbumHeaderImage :src="albumStore.album.preFormattedData.url" :focus-x="focusX" :focus-y="focusY" />
			<div class="absolute right-1 top-1 flex flex-col gap-2 z-100">
				<Button
					v-if="albumStore.rights?.can_edit && mode === 'normal'"
					@click="enableEditMode"
					class="bg-(--p-toolbar-background)/60 rounded-lg border-0 px-2 py-1 text-white hover:bg-white/30 cursor-pointer"
					:label="$t('gallery.album.hero.edit')"
				/>
				<template v-if="albumStore.rights?.can_edit && mode === 'edit'">
					<Button
						@click="saveChanges"
						class="bg-(--p-toolbar-background)/60 rounded-lg border-0 px-2 py-1 text-white hover:bg-white/30 cursor-pointer"
						:label="$t('gallery.album.hero.save')"
					/>
					<Button
						@click="isFocusPickerVisible = true"
						class="bg-(--p-toolbar-background)/60 rounded-lg border-0 px-2 py-1 text-white hover:bg-white/30 cursor-pointer"
						:label="$t('gallery.set_focus')"
					/>
				</template>
			</div>

			<HeaderFocusPicker
				v-if="isFocusPickerVisible"
				:src="albumStore.album.preFormattedData.url"
				:focus-x="focusX"
				:focus-y="focusY"
				@update:focus="updateFocus"
				@close="isFocusPickerVisible = false"
				@cancel="cancelFocus"
			/>
			<div
				id="coverDetails"
				class="relative text-shadow-sm w-full bg-linear-to-b from-black/20 via-transparent via-80% to-black/40 h-full transition-all duration-300 grid"
				:class="positionClasses"
			>
				<div class="relative">
					<Button
						v-if="mode === 'edit'"
						:class="[
							'h-8',
							'w-8',
							'bg-(--p-toolbar-background)/60 rounded-md border-0 cursor-pointer hover:bg-white/30 ',
							'top-1/2 -left-16 absolute! text-white',
						]"
						@click="setColor(selectedColorIndex - 1)"
						icon="pi pi-chevron-left"
					/>
					<Button
						v-if="mode === 'edit'"
						:class="[
							'h-8',
							'w-8',
							'bg-(--p-toolbar-background)/60 rounded-md border-0 cursor-pointer hover:bg-white/30 ',
							'top-1/2 -right-16 absolute! text-white',
						]"
						@click="setColor(selectedColorIndex + 1)"
						icon="pi pi-chevron-right"
					/>
					<div
						v-if="mode === 'edit'"
						class="bg-(--p-toolbar-background)/60 text-white rounded-lg p-1 flex flex-row grow left-1/2 -translate-x-1/2 absolute top-5 -translate-y-1/2 z-10 w-70"
					>
						<HeaderEditButton @setPosition="setPosition('top-left')" :position="'top-left'" />
						<HeaderEditButton @setPosition="setPosition('bottom-left')" :position="'bottom-left'" />
						<HeaderEditButton @setPosition="setPosition('center')" :position="'center'" />
						<HeaderEditButton @setPosition="setPosition('top-right')" :position="'top-right'" />
						<HeaderEditButton @setPosition="setPosition('bottom-right')" :position="'bottom-right'" />
					</div>

					<h1
						class="text-4xl sm:text-6xl tracking-widest leading-snug font-bold uppercase m-0 wrap-break-word mb-2"
						:style="{ color: selectedColor }"
					>
						{{ album.title }}
					</h1>
					<span
						v-if="album.preFormattedData.min_max_text"
						class="block text-xl sm:text-sm tracking-widest leading-snug font-bold uppercase m-0 wrap-break-word mb-2"
						:style="{ color: selectedColor }"
					>
						{{ album.preFormattedData.min_max_text }}
					</span>
					<button
						class="mt-4 sm:mt-2 inline-block px-6 py-3 sm:px-10 sm:py-4 lg:px-12 lg:py-4 text-2xl sm:text-sm lg:text-xs font-semibold tracking-wide sm:tracking-wider uppercase cursor-pointer hover:bg-white/10 w-fit border"
						:style="{ color: selectedColor, borderColor: selectedColor }"
						@click="scrollToPictures"
					>
						{{ $t("gallery.album.hero.open_gallery") }}
					</button>
				</div>
			</div>
			<span
				v-if="initdata && is_album_header_landing_title_enabled"
				:class="[
					'author text-center w-full uppercase absolute left-0 z-50 text-shadow-sm',
					album_header_size === 'half_screen' ? 'bottom-6' : 'bottom-28',
				]"
				:style="{ color: selectedColor }"
			>
				{{ initdata.landing_title }}
			</span>
		</template>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import { useAlbumStore } from "@/stores/AlbumState";
import AlbumService, { UpdateAlbumHeaderData } from "@/services/album-service";
import HeaderEditButton from "./HeaderEditButton.vue";
import AlbumHeaderImage from "./AlbumHeaderImage.vue";
import HeaderFocusPicker from "./HeaderFocusPicker.vue";
import InitService from "@/services/init-service";
import { storeToRefs } from "pinia";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import Button from "primevue/button";

const lycheeStore = useLycheeStateStore();
const albumStore = useAlbumStore();
const toast = useToast();

const { is_album_enhanced_display_enabled, album_header_size, is_album_header_landing_title_enabled } = storeToRefs(lycheeStore);

const emits = defineEmits<{
	setPosition: [position: string];
	scrollToPictures: [];
}>();

const props = defineProps<{
	album:
		| App.Http.Resources.Models.HeadAlbumResource
		| App.Http.Resources.Models.HeadTagAlbumResource
		| App.Http.Resources.Models.HeadSmartAlbumResource;
}>();

const POSITION_CLASSES = {
	"top-left": "justify-items-start items-start text-left pt-20 pl-20",
	"top-right": "justify-items-end items-start text-right pt-20 pr-20",
	"bottom-left": "justify-items-start items-end text-left pb-40 pl-20",
	"bottom-right": "justify-items-end items-end text-right pb-40 pr-20",
	center: "justify-items-center items-center text-center",
};

const COLORS = computed(() => {
	const p = props.album.preFormattedData.palette as Record<string, string> | null;
	return [
		"white",
		"black",
		p?.colour_1 ?? "#2563eb",
		p?.colour_2 ?? "#dc2626",
		p?.colour_3 ?? "#16a34a",
		p?.colour_4 ?? "#9333ea",
		p?.colour_5 ?? "#ca8a04",
	];
});

const selectedColor = computed(() => COLORS.value[selectedColorIndex.value]);

const COLOR_ENUMS = ["white", "black", "colour_1", "colour_2", "colour_3", "colour_4", "colour_5"] as App.Enum.AlbumTitleColor[];

const POSITION_ENUMS: Record<string, App.Enum.AlbumTitlePosition> = {
	"top-left": "top_left",
	"top-right": "top_right",
	"bottom-left": "bottom_left",
	"bottom-right": "bottom_right",
	center: "center",
};

const selectedColorIndex = ref(0);
const position = ref<keyof typeof POSITION_CLASSES>("top-left");
const mode = ref("normal");
const positionClasses = computed(() => POSITION_CLASSES[position.value]);

const isFocusPickerVisible = ref(false);
const focusX = ref<number | null>(null);
const focusY = ref<number | null>(null);

function initFromProps() {
	if (props.album.preFormattedData?.title_color) {
		const colorEnum = props.album.preFormattedData.title_color as App.Enum.AlbumTitleColor;
		const index = COLOR_ENUMS.indexOf(colorEnum);
		if (index !== -1) {
			selectedColorIndex.value = index;
		}
	} else {
		selectedColorIndex.value = 0; // Default to white
	}

	if (props.album.preFormattedData?.title_position) {
		const posEnum = props.album.preFormattedData.title_position as App.Enum.AlbumTitlePosition;
		// Reverse mapping from enum to key
		const key = Object.keys(POSITION_ENUMS).find((k) => POSITION_ENUMS[k] === posEnum);
		if (key) {
			position.value = key as keyof typeof POSITION_CLASSES;
		} else {
			position.value = "top-left";
		}
	} else {
		position.value = "top-left";
	}

	if (props.album.preFormattedData?.header_photo_focus) {
		focusX.value = props.album.preFormattedData.header_photo_focus.x;
		focusY.value = props.album.preFormattedData.header_photo_focus.y;
	} else {
		focusX.value = null;
		focusY.value = null;
	}
}

watch(
	() => props.album,
	() => {
		initFromProps();
	},
	{ deep: true },
);

onMounted(() => {
	initFromProps();
});

function enableEditMode() {
	initFromProps();
	mode.value = "edit";
}

function saveChanges() {
	const payload: UpdateAlbumHeaderData = {
		album_id: props.album.id,
		title_color: COLOR_ENUMS[selectedColorIndex.value],
		title_position: POSITION_ENUMS[position.value],
		header_photo_focus: focusX.value !== null && focusY.value !== null ? { x: focusX.value, y: focusY.value } : { x: 0, y: 0 },
	};

	AlbumService.updateAlbumHeader(payload)
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("toasts.album_updated"), life: 3000 });
			mode.value = "normal";
			AlbumService.clearCache(props.album.id);
			albumStore.loadHead();
		})
		.catch((e) => {
			console.error(e);
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("toasts.update_failed"), life: 3000 });
		});
}

function updateFocus(x: number, y: number) {
	focusX.value = x;
	focusY.value = y;
}

function cancelFocus() {
	isFocusPickerVisible.value = false;
	// Revert to saved state
	initFromProps();
}

function setPosition(newPosition: string) {
	position.value = newPosition as keyof typeof POSITION_CLASSES;
	emits("setPosition", newPosition);
}

function setColor(colorIndex: number) {
	if (colorIndex < 0) {
		selectedColorIndex.value = COLORS.value.length - 1;
	} else if (colorIndex >= COLORS.value.length) {
		selectedColorIndex.value = 0;
	} else {
		selectedColorIndex.value = colorIndex;
	}
}

function scrollToPictures() {
	emits("scrollToPictures");
}

const initdata = ref<App.Http.Resources.GalleryConfigs.LandingPageResource | undefined>(undefined);
InitService.fetchLandingData()
	.then((data) => {
		initdata.value = data.data;
	})
	.catch((e) => {
		console.error(e);
	});
</script>
