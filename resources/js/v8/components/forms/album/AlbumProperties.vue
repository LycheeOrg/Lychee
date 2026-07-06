<template>
	<UCard class="sm:p-4 xl:px-9 max-sm:w-full sm:min-w-3xl shrink-0" :ui="{ body: 'p-0' }">
		<form class="flex flex-col gap-4">
			<UFormField :label="$t('gallery.album.properties.title')">
				<UInput id="title" v-model="title" type="text" class="w-full" />
			</UFormField>
			<div v-if="is_se_enabled || is_se_preview_enabled" dir="ltr" class="flex items-center gap-1">
				<UTooltip :text="$t('gallery.album.properties.copy_slug_url')">
					<div class="text-muted flex items-center" :class="{ 'cursor-pointer': slug }" @click="copySlugUrl">
						<span>{{ Constants.BASE_URL }}/gallery/</span>
					</div>
				</UTooltip>
				<UInput id="slug" v-model="slugForInput" type="text" :disabled="is_se_preview_enabled" class="flex-1" />
				<UTooltip :text="$t('gallery.album.properties.generate_slug')">
					<UButton icon="prime:sync" variant="ghost" color="primary" :disabled="is_se_preview_enabled" @click="generateSlug" />
				</UTooltip>
			</div>
			<UFormField :label="$t('gallery.album.properties.description')">
				<UTextarea id="description" v-model="descriptionForInput" class="w-full" :rows="6" />
			</UFormField>
			<div class="flex gap-4 flex-wrap">
				<UFormField :label="$t('gallery.album.properties.photo_ordering')">
					<USelectMenu v-model="photoSortingColumn" :items="photoSortingColumnsOptions" label-key="label" class="w-62">
						<template #item-label="{ item }">{{ $t(item.label) }}</template>
					</USelectMenu>
				</UFormField>
				<UFormField :label="$t('gallery.album.properties.asc/desc')">
					<USelectMenu v-model="photoSortingOrder" :items="sortingOrdersOptions" label-key="label" class="w-62">
						<template #item-label="{ item }">{{ $t(item.label) }}</template>
					</USelectMenu>
				</UFormField>
			</div>
			<template v-if="is_model_album">
				<div class="flex gap-4 flex-wrap">
					<UFormField :label="$t('gallery.album.properties.children_ordering')">
						<USelectMenu v-model="albumSortingColumn" :items="albumSortingColumnsOptions" label-key="label" class="w-62">
							<template #item-label="{ item }">{{ $t(item.label) }}</template>
						</USelectMenu>
					</UFormField>
					<UFormField :label="$t('gallery.album.properties.asc/desc')">
						<USelectMenu v-model="albumSortingOrder" :items="sortingOrdersOptions" label-key="label" class="w-62">
							<template #item-label="{ item }">{{ $t(item.label) }}</template>
						</USelectMenu>
					</UFormField>
				</div>
				<UFormField :label="$t('gallery.album.properties.header')">
					<USelectMenu v-model="header_id" :items="headersOptions" label-key="title" class="w-72">
						<template #item-leading="{ item }">
							<UIcon v-if="item.id === 'compact'" name="prime:arrow-down-left-and-arrow-up-right-to-center" />
							<img v-else :src="item.thumb ?? undefined" alt="poster" class="w-4 rounded-sm" />
						</template>
					</USelectMenu>
				</UFormField>
				<UFormField :label="$t('gallery.album.properties.license')">
					<USelectMenu v-model="license" :items="licenseOptions" label-key="label" class="w-72">
						<template #item-label="{ item }">{{ $t(item.label) }}</template>
					</USelectMenu>
				</UFormField>
				<UFormField :label="$t('gallery.album.properties.copyright')">
					<UInput id="copyright" v-model="copyright" class="w-full" />
				</UFormField>
				<div class="flex flex-wrap gap-4">
					<UFormField :label="$t('gallery.album.properties.aspect_ratio')">
						<USelectMenu v-model="aspectRatio" :items="aspectRatioOptions" label-key="label" class="w-72">
							<template #item-label="{ item }">{{ $t(item.label) }}</template>
						</USelectMenu>
					</UFormField>
					<UFormField :label="$t('gallery.album.properties.album_timeline')">
						<USelectMenu v-model="albumTimeline" :items="albumTimelineOptions" label-key="label" class="w-72">
							<template #item-label="{ item }">{{ $t(item.label) }}</template>
						</USelectMenu>
					</UFormField>
				</div>
			</template>
			<div class="flex flex-wrap gap-4">
				<UFormField :label="$t('gallery.album.properties.layout')">
					<USelectMenu v-model="photoLayout" :items="photoLayoutOptions" label-key="label" class="w-72">
						<template #item-label="{ item }">{{ $t(item.label) }}</template>
					</USelectMenu>
				</UFormField>
				<UFormField :label="$t('gallery.album.properties.photo_timeline')">
					<USelectMenu v-model="photoTimeline" :items="photoTimelineOptions" label-key="label" class="w-72">
						<template #item-label="{ item }">{{ $t(item.label) }}</template>
					</USelectMenu>
				</UFormField>
			</div>

			<div v-if="!is_model_album && !is_person_album" class="flex flex-col gap-2">
				<UFormField :label="$t('gallery.album.properties.show_tags')">
					<TagsInput v-model="tags" :add="false" />
				</UFormField>
				<div class="flex gap-2 items-center my-2">
					<USwitch v-model="is_and" input-id="pp_is_and" />
					<label for="pp_is_and" class="text-highlighted">{{ $t("gallery.album.properties.all_tags_must_match") }}</label>
				</div>
			</div>
			<div v-if="is_person_album" class="flex flex-col gap-2">
				<UFormField :label="$t('dialogs.new_person_album.set_persons')">
					<PersonsInput v-model="selectedPersons" :placeholder="$t('dialogs.new_person_album.set_persons')" />
				</UFormField>
				<div class="flex gap-2 items-center my-2">
					<USwitch v-model="is_and" input-id="pp_is_and" />
					<label for="pp_is_and" class="text-highlighted">{{ $t("gallery.album.properties.all_persons_must_match") }}</label>
				</div>
			</div>
			<UButton class="mt-4 w-full font-bold justify-center" color="primary" @click="save">
				{{ $t("dialogs.button.save") }}
			</UButton>
		</form>
	</UCard>
</template>
<script setup lang="ts">
import Constants from "@/services/constants";
import { computed, onMounted, ref, watch } from "vue";
import AlbumService, { UpdateAbumData, UpdateTagAlbumData, UpdatePersonAlbumData } from "@/services/album-service";
import PersonsInput from "@/v8/components/forms/basic/PersonsInput.vue";
import {
	photoSortingColumnsOptions,
	albumSortingColumnsOptions,
	sortingOrdersOptions,
	licenseOptions,
	aspectRatioOptions,
	photoLayoutOptions,
	SelectOption,
	SelectBuilders,
	timelinePhotoGranularityOptions,
	timelineAlbumGranularityOptions,
} from "@/config/constants";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import TagsInput from "@/v8/components/forms/basic/TagsInput.vue";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumStore } from "@/stores/AlbumState";

type HeaderOption = {
	id: string;
	title?: string;
	thumb?: string | null;
};

const LycheeState = useLycheeStateStore();
const albumStore = useAlbumStore();
const { is_se_enabled, is_se_preview_enabled } = storeToRefs(LycheeState);

const photosStore = usePhotosStore();

const toast = useAppToast();
const is_model_album = ref(true);
const albumId = ref("");
const title = ref("");
const slug = ref<string | null>(null);

function copySlugUrl() {
	if (slug.value === null || slug.value.trim() === "") {
		return;
	}

	const url = Constants.BASE_URL + "/gallery/" + slug.value;
	navigator.clipboard.writeText(url).then(() => {
		toast.add({ severity: "success", summary: trans("dialogs.share_album.url_copied"), life: 2000 });
	});
}

function generateSlug() {
	if (title.value === null || title.value.trim() === "") {
		return;
	}
	slug.value = title.value
		.toLowerCase()
		.replace(/&/g, "and")
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/-+/g, "-")
		.replace(/^-|-$/g, "")
		.replace(/^[0-9]+/, "")
		.replace(/^-/, "")
		.substring(0, 250);
}
const description = ref<string | null>(null);
// UInput/UTextarea's v-model requires `string | undefined` (no null); slug/description carry
// `null` throughout the rest of this component's logic and payload construction.
const slugForInput = computed<string | undefined>({
	get: () => slug.value ?? undefined,
	set: (v) => {
		slug.value = v ?? null;
	},
});
const descriptionForInput = computed<string | undefined>({
	get: () => description.value ?? undefined,
	set: (v) => {
		description.value = v ?? null;
	},
});
const photoSortingColumn = ref<SelectOption<App.Enum.ColumnSortingPhotoType> | undefined>(undefined);
const photoSortingOrder = ref<SelectOption<App.Enum.OrderSortingType> | undefined>(undefined);
const albumSortingColumn = ref<SelectOption<App.Enum.ColumnSortingAlbumType> | undefined>(undefined);
const albumSortingOrder = ref<SelectOption<App.Enum.OrderSortingType> | undefined>(undefined);
const photoLayout = ref<SelectOption<App.Enum.PhotoLayoutType> | undefined>(undefined);
const photoTimeline = ref<SelectOption<App.Enum.TimelinePhotoGranularity> | undefined>(undefined);
const albumTimeline = ref<SelectOption<App.Enum.TimelineAlbumGranularity> | undefined>(undefined);
const license = ref<SelectOption<App.Enum.LicenseType> | undefined>(undefined);
const copyright = ref<string | undefined>(undefined);
const tags = ref<string[]>([]);
const selectedPersons = ref<App.Http.Resources.Models.PersonResource[]>([]);
const is_person_album = ref<boolean>(false);
const aspectRatio = ref<SelectOption<App.Enum.AspectRatioType> | undefined>(undefined);
const header_id = ref<HeaderOption | undefined>(undefined);
const is_and = ref<boolean>(false);

const photoTimelineOptions = computed(() => {
	if (is_se_enabled.value) {
		return timelinePhotoGranularityOptions;
	}

	return timelinePhotoGranularityOptions.slice(0, 2);
});

const albumTimelineOptions = computed(() => {
	if (is_se_enabled.value) {
		return timelineAlbumGranularityOptions;
	}

	return timelineAlbumGranularityOptions.slice(0, 2);
});

const headersOptions = computed(() => {
	const list: HeaderOption[] = [
		{
			id: "compact",
			title: trans("gallery.album.properties.compact_header"),
		},
	];
	list.push(
		...photosStore.photos.map((photo) => ({
			id: photo.id,
			title: photo.title,
			thumb: photo.size_variants.thumb?.url,
		})),
	);
	return list;
});

function buildHeaderId(value: string | null, photos: App.Http.Resources.Models.PhotoResource[]): HeaderOption | undefined {
	if (value === null) {
		return undefined;
	}
	if (value === "compact") {
		return { id: "compact" };
	}
	const photo = photos.find((photo) => photo.id === value);
	if (photo === undefined) {
		return undefined;
	}
	return {
		id: photo.id,
		title: photo.title,
		thumb: photo.size_variants.thumb?.url,
	};
}

function load(editable: App.Http.Resources.Editable.EditableBaseAlbumResource, photos: App.Http.Resources.Models.PhotoResource[]) {
	is_model_album.value = editable.is_model_album;
	albumId.value = editable.id;
	title.value = editable.title;
	slug.value = editable.slug;
	description.value = editable.description;
	photoSortingColumn.value = SelectBuilders.buildPhotoSorting(editable.photo_sorting?.column);
	photoSortingOrder.value = SelectBuilders.buildSortingOrder(editable.photo_sorting?.order);
	albumSortingColumn.value = SelectBuilders.buildAlbumSorting(editable.album_sorting?.column);
	albumSortingOrder.value = SelectBuilders.buildSortingOrder(editable.album_sorting?.order);
	photoLayout.value = SelectBuilders.buildPhotoLayout(editable.photo_layout ?? undefined);
	license.value = SelectBuilders.buildLicense(editable.license ?? undefined);
	aspectRatio.value = SelectBuilders.buildAspectRatio(editable.aspect_ratio ?? undefined);
	albumTimeline.value = SelectBuilders.buildTimelineAlbumGranularity(editable.album_timeline ?? undefined);
	photoTimeline.value = SelectBuilders.buildTimelinePhotoGranularity(editable.photo_timeline ?? undefined);
	header_id.value = buildHeaderId(editable.header_id, photos);
	tags.value = editable.tags;
	is_and.value = editable.is_and ?? false;

	if (editable.persons && editable.persons.length > 0) {
		is_person_album.value = true;
		selectedPersons.value = editable.persons as App.Http.Resources.Models.PersonResource[];
	} else {
		is_person_album.value = false;
	}
}

onMounted(() => {
	LycheeState.load();
	if (albumStore.tagOrModelAlbum?.editable !== undefined && albumStore.tagOrModelAlbum?.editable !== null) {
		load(albumStore.tagOrModelAlbum.editable, photosStore.photos);
	}
});

function save() {
	if (is_model_album.value) {
		saveAlbum();
		return;
	}
	if (is_person_album.value) {
		savePersonAlbum();
		return;
	}
	saveTagAlbum();
}

function saveAlbum() {
	const data: UpdateAbumData = {
		album_id: albumId.value,
		title: title.value,
		slug: slug.value === "" ? null : slug.value,
		license: license.value?.value ?? null,
		description: description.value,
		photo_sorting_column: photoSortingColumn.value?.value ?? null,
		photo_sorting_order: photoSortingOrder.value?.value ?? null,
		album_sorting_column: albumSortingColumn.value?.value ?? null,
		album_sorting_order: albumSortingOrder.value?.value ?? null,
		album_aspect_ratio: aspectRatio.value?.value ?? null,
		copyright: copyright.value ?? null,
		header_id: header_id.value?.id === "compact" ? null : (header_id.value?.id ?? null),
		is_compact: header_id.value?.id === "compact",
		photo_layout: photoLayout.value?.value ?? null,
		album_timeline: albumTimeline.value?.value ?? null,
		photo_timeline: photoTimeline.value?.value ?? null,
		is_pinned: albumStore.tagOrModelAlbum?.editable?.is_pinned ?? false,
	};
	AlbumService.updateAlbum(data).then(() => {
		toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
		AlbumService.clearCache(albumId.value);
		albumStore.loadHead();
	});
}

function saveTagAlbum() {
	if (tags.value.length === 0) {
		toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("gallery.album.properties.tags_required"), life: 3000 });
		return;
	}

	const data: UpdateTagAlbumData = {
		album_id: albumId.value,
		title: title.value,
		slug: slug.value === "" ? null : slug.value,
		tags: tags.value,
		description: description.value,
		photo_sorting_column: photoSortingColumn.value?.value ?? null,
		photo_sorting_order: photoSortingOrder.value?.value ?? null,
		copyright: copyright.value ?? null,
		photo_layout: photoLayout.value?.value ?? null,
		photo_timeline: photoTimeline.value?.value ?? null,
		is_pinned: albumStore.tagOrModelAlbum?.editable?.is_pinned ?? false,
		is_and: is_and.value,
	};
	AlbumService.updateTag(data).then(() => {
		toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
		AlbumService.clearCache(albumId.value);
		albumStore.loadHead();
	});
}

function savePersonAlbum() {
	if (selectedPersons.value.length === 0) {
		toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("gallery.album.properties.persons_required"), life: 3000 });
		return;
	}

	const data: UpdatePersonAlbumData = {
		album_id: albumId.value,
		title: title.value,
		slug: slug.value === "" ? null : slug.value,
		persons: selectedPersons.value.map((p) => p.id),
		description: description.value,
		photo_sorting_column: photoSortingColumn.value?.value ?? null,
		photo_sorting_order: photoSortingOrder.value?.value ?? null,
		copyright: copyright.value ?? null,
		photo_layout: photoLayout.value?.value ?? null,
		photo_timeline: photoTimeline.value?.value ?? null,
		is_pinned: albumStore.tagOrModelAlbum?.editable?.is_pinned ?? false,
		is_and: is_and.value,
	};
	AlbumService.updatePerson(data).then(() => {
		toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
		AlbumService.clearCache(albumId.value);
		albumStore.loadHead();
	});
}

watch(
	() => [albumStore.tagOrModelAlbum?.editable, photosStore.photos],
	([editable, photos]) => {
		if (editable !== null && editable !== undefined) {
			load(editable as App.Http.Resources.Editable.EditableBaseAlbumResource, photos as App.Http.Resources.Models.PhotoResource[]);
		}
	},
);
</script>
