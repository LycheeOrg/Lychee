<template>
	<Card class="sm:p-4 xl:px-9 max-sm:w-full sm:min-w-3xl shrink-0" :pt:body:class="'p-0'">
		<template #content>
			<form>
				<div class="h-12">
					<FloatLabel variant="on">
						<InputText id="title" v-model="title" type="text" />
						<label for="title">{{ $t("gallery.album.properties.title") }}</label>
					</FloatLabel>
				</div>
				<div v-if="is_se_enabled || is_se_preview_enabled" class="h-12 mt-2" dir="ltr">
					<InputGroup class="rounded-none">
						<div
							class="text-muted-color flex items-center"
							:class="{ 'cursor-pointer': slug }"
							@click="copySlugUrl"
							v-tooltip.top="{ value: $t('gallery.album.properties.copy_slug_url'), pt: { root: slug ? '' : 'hidden!' } }"
						>
							<span>{{ Constants.BASE_URL }}/gallery/</span>
						</div>
						<FloatLabel variant="on">
							<InputText id="slug" v-model="slug" type="text" :disabled="is_se_preview_enabled" class="pl-1" />
							<label for="slug" :class="{ 'text-primary-500': is_se_preview_enabled }">{{ $t("gallery.album.properties.slug") }}</label>
						</FloatLabel>
						<Button
							icon="pi pi-sync"
							text
							severity="primary"
							:disabled="is_se_preview_enabled"
							@click="generateSlug"
							v-tooltip.top="$t('gallery.album.properties.generate_slug')"
						/>
					</InputGroup>
				</div>
				<div class="my-4 h-48">
					<FloatLabel variant="on">
						<Textarea id="description" v-model="description" class="w-full h-48" :rows="6" :cols="30" />
						<label for="description">{{ $t("gallery.album.properties.description") }}</label>
					</FloatLabel>
				</div>
				<div class="my-2 h-10 flex">
					<FloatLabel variant="on">
						<Select
							label-id="photoSortingColumn"
							v-model="photoSortingColumn"
							class="w-62 border-none"
							:options="photoSortingColumnsOptions"
							option-label="label"
							show-clear
						>
							<template #value="slotProps">
								<div v-if="slotProps.value" class="flex items-center">
									<div>{{ $t(slotProps.value.label) }}</div>
								</div>
							</template>
							<template #option="slotProps">
								<div class="flex items-center">
									<div>{{ $t(slotProps.option.label) }}</div>
								</div>
							</template>
						</Select>
						<label for="photoSortingColumn">{{ $t("gallery.album.properties.photo_ordering") }}</label>
					</FloatLabel>
					<FloatLabel variant="on">
						<Select
							label-id="photoSortingOrder"
							v-model="photoSortingOrder"
							class="w-62 border-none"
							:options="sortingOrdersOptions"
							option-label="label"
							show-clear
						>
							<template #value="slotProps">
								<div v-if="slotProps.value" class="flex items-center">
									<div>{{ $t(slotProps.value.label) }}</div>
								</div>
							</template>
							<template #option="slotProps">
								<div class="flex items-center">
									<div>{{ $t(slotProps.option.label) }}</div>
								</div>
							</template>
						</Select>
						<label for="photoSortingOrder">{{ $t("gallery.album.properties.asc/desc") }}</label>
					</FloatLabel>
				</div>
				<template v-if="is_model_album">
					<div class="my-2 h-10 flex">
						<FloatLabel variant="on">
							<Select
								label-id="albumSortingColumn"
								v-model="albumSortingColumn"
								class="w-62 border-none"
								:options="albumSortingColumnsOptions"
								option-label="label"
								show-clear
							>
								<template #value="slotProps">
									<div v-if="slotProps.value" class="flex items-center">
										<div>{{ $t(slotProps.value.label) }}</div>
									</div>
								</template>
								<template #option="slotProps">
									<div class="flex items-center">
										<div>{{ $t(slotProps.option.label) }}</div>
									</div>
								</template>
							</Select>
							<label for="albumSortingColumn">{{ $t("gallery.album.properties.children_ordering") }}</label>
						</FloatLabel>
						<FloatLabel variant="on">
							<Select
								label-id="albumSortingOrder"
								v-model="albumSortingOrder"
								class="w-62 border-none"
								:options="sortingOrdersOptions"
								option-label="label"
								show-clear
							>
								<template #value="slotProps">
									<div v-if="slotProps.value" class="flex items-center">
										<div>{{ $t(slotProps.value.label) }}</div>
									</div>
								</template>
								<template #option="slotProps">
									<div class="flex items-center">
										<div>{{ $t(slotProps.option.label) }}</div>
									</div>
								</template>
							</Select>
							<label for="albumSortingOrder">{{ $t("gallery.album.properties.asc/desc") }}</label>
						</FloatLabel>
					</div>
					<div class="h-10 my-2">
						<FloatLabel variant="on">
							<Select
								label-id="header"
								v-model="header_id"
								class="w-72 border-none"
								:options="headersOptions"
								option-label="title"
								show-clear
							>
								<template #value="slotProps">
									<div v-if="slotProps.value && slotProps.value.id === 'compact'">
										<i class="pi pi-arrow-down-left-and-arrow-up-right-to-center" />
										<span class="ltr:ml-4 rtl:mr-4">{{ $t("gallery.album.properties.compact_header") }}</span>
									</div>
									<div v-else-if="slotProps.value" class="flex items-center">
										<img :src="slotProps.value.thumb" alt="poster" class="w-4 rounded-sm" />
										<span class="ltr:ml-4 rtl:mr-4">{{ slotProps.value.title }}</span>
									</div>
								</template>
								<template #option="slotProps">
									<div v-if="slotProps.option.id === 'compact'" class="flex items-center">
										<i class="pi pi-arrow-down-left-and-arrow-up-right-to-center" />
										<span class="ltr:ml-4 rtl:mr-4">{{ $t("gallery.album.properties.compact_header") }}</span>
									</div>
									<div v-else class="flex items-center">
										<img :src="slotProps.option.thumb" alt="poster" class="w-4 rounded-sm" />
										<span class="ltr:ml-4 rtl:mr-4">{{ slotProps.option.title }}</span>
									</div>
								</template>
							</Select>
							<label for="header">{{ $t("gallery.album.properties.header") }}</label>
						</FloatLabel>
					</div>
					<div class="h-10 my-2">
						<FloatLabel variant="on">
							<Select
								label-id="license"
								v-model="license"
								class="w-72 border-none"
								:options="licenseOptions"
								option-label="label"
								show-clear
							>
								<template #value="slotProps">
									<div v-if="slotProps.value" class="flex items-center">
										<div>{{ $t(slotProps.value.label) }}</div>
									</div>
								</template>
								<template #option="slotProps">
									<div class="flex items-center">
										<div>{{ $t(slotProps.option.label) }}</div>
									</div>
								</template>
							</Select>
							<label for="license">{{ $t("gallery.album.properties.license") }}</label>
						</FloatLabel>
					</div>
					<div class="my-2">
						<FloatLabel variant="on">
							<InputText id="copyright" v-model="copyright" />
							<label for="copyright">{{ $t("gallery.album.properties.copyright") }}</label>
						</FloatLabel>
					</div>
					<div class="sm:h-10 my-2 pt-4 flex flex-wrap gap-y-4">
						<FloatLabel variant="on">
							<Select
								label-id="aspectRatio"
								v-model="aspectRatio"
								class="w-72 border-none"
								:options="aspectRationOptions"
								option-label="label"
								show-clear
							>
								<template #value="slotProps">
									<div v-if="slotProps.value" class="flex items-center">
										<div>{{ $t(slotProps.value.label) }}</div>
									</div>
								</template>
								<template #option="slotProps">
									<div class="flex items-center">
										<div>{{ $t(slotProps.option.label) }}</div>
									</div>
								</template>
							</Select>
							<label for="aspectRatio">{{ $t("gallery.album.properties.aspect_ratio") }}</label>
						</FloatLabel>
						<FloatLabel variant="on">
							<Select
								label-id="albumTimeline"
								v-model="albumTimeline"
								class="w-72 border-none"
								:options="albumTimelineOptions"
								option-label="label"
								show-clear
							>
								<template #value="slotProps">
									<div v-if="slotProps.value" class="flex items-center">
										<div>{{ $t(slotProps.value.label) }}</div>
									</div>
								</template>
								<template #option="slotProps">
									<div class="flex items-center">
										<div>{{ $t(slotProps.option.label) }}</div>
									</div>
								</template>
							</Select>
							<label for="albumTimeline">{{ $t("gallery.album.properties.album_timeline") }}</label>
						</FloatLabel>
					</div>
				</template>
				<div class="sm:h-10 my-2 pt-4 flex flex-wrap gap-y-4">
					<FloatLabel variant="on">
						<Select
							label-id="photoLayout"
							v-model="photoLayout"
							class="w-72 border-none"
							:options="photoLayoutOptions"
							option-label="label"
							show-clear
						>
							<template #value="slotProps">
								<div v-if="slotProps.value" class="flex items-center">
									<div>{{ $t(slotProps.value.label) }}</div>
								</div>
							</template>
							<template #option="slotProps">
								<div class="flex items-center">
									<div>{{ $t(slotProps.option.label) }}</div>
								</div>
							</template>
						</Select>
						<label for="photoLayout">{{ $t("gallery.album.properties.layout") }}</label>
					</FloatLabel>
					<FloatLabel variant="on">
						<Select
							label-id="photoTimeline"
							v-model="photoTimeline"
							class="w-72 border-none"
							:options="photoTimelineOptions"
							option-label="label"
							show-clear
						>
							<template #value="slotProps">
								<div v-if="slotProps.value" class="flex items-center">
									<div>{{ $t(slotProps.value.label) }}</div>
								</div>
							</template>
							<template #option="slotProps">
								<div class="flex items-center">
									<div>{{ $t(slotProps.option.label) }}</div>
								</div>
							</template>
						</Select>
						<label for="photoTimeline">{{ $t("gallery.album.properties.photo_timeline") }}</label>
					</FloatLabel>
				</div>

				<div v-if="is_model_album" class="h-10 my-2 pt-4"></div>

				<div v-if="!is_model_album" class="my-4 flex flex-col gap-2">
					<FloatLabel variant="on">
						<TagsInput v-model="tags" :add="false" />
						<label for="tags">{{ $t("gallery.album.properties.show_tags") }}</label>
					</FloatLabel>
					<div class="flex gap-2 items-center my-2">
						<ToggleSwitch v-model="is_and" input-id="pp_is_and" />
						<label for="pp_is_and" class="text-muted-color-emphasis">{{ $t("gallery.album.properties.all_tags_must_match") }}</label>
					</div>
				</div>
				<Button class="p-3 mt-4 w-full font-bold border-none shrink" @click="save">
					{{ $t("dialogs.button.save") }}
				</Button>
			</form>
		</template>
	</Card>
</template>
<script setup lang="ts">
import Constants from "@/services/constants";
import { computed, onMounted, ref, watch } from "vue";
import Button from "primevue/button";
import Card from "primevue/card";
import Select from "primevue/select";
import FloatLabel from "primevue/floatlabel";
import InputText from "@/components/forms/basic/InputText.vue";
import Textarea from "@/components/forms/basic/Textarea.vue";
import AlbumService, { UpdateAbumData, UpdateTagAlbumData } from "@/services/album-service";
import {
	photoSortingColumnsOptions,
	albumSortingColumnsOptions,
	sortingOrdersOptions,
	licenseOptions,
	aspectRationOptions,
	photoLayoutOptions,
	SelectOption,
	SelectBuilders,
	timelinePhotoGranularityOptions,
	timelineAlbumGranularityOptions,
} from "@/config/constants";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import TagsInput from "@/components/forms/basic/TagsInput.vue";
import ToggleSwitch from "primevue/toggleswitch";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumStore } from "@/stores/AlbumState";
import InputGroup from "primevue/inputgroup";

type HeaderOption = {
	id: string;
	title?: string;
	thumb?: string | null;
};

const LycheeState = useLycheeStateStore();
const albumStore = useAlbumStore();
const { is_se_enabled, is_se_preview_enabled } = storeToRefs(LycheeState);

const photosStore = usePhotosStore();

const toast = useToast();
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

watch(
	() => [albumStore.tagOrModelAlbum?.editable, photosStore.photos],
	([editable, photos]) => {
		if (editable !== null && editable !== undefined) {
			load(editable as App.Http.Resources.Editable.EditableBaseAlbumResource, photos as App.Http.Resources.Models.PhotoResource[]);
		}
	},
);
</script>
