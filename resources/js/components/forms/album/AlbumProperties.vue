<template>
	<Card class="sm:p-4 xl:px-9 max-sm:w-full sm:min-w-[32rem] flex-shrink-0">
		<template #content>
			<form>
				<div class="h-12">
					<FloatLabel variant="on">
						<InputText id="title" type="text" v-model="title" />
						<label for="title">{{ $t("lychee.ALBUM_TITLE") }}</label>
					</FloatLabel>
					<!-- <x-forms.error-message field='title' /> -->
				</div>
				<div class="my-4 h-48">
					<FloatLabel variant="on">
						<Textarea id="description" class="w-full h-48" v-model="description" rows="6" cols="30" />
						<!-- <x-forms.error-message field='description' /> -->
						<label for="description">{{ $t("lychee.ALBUM_DESCRIPTION") }}</label>
					</FloatLabel>
				</div>
				<div class="my-2 h-10 flex">
					<FloatLabel variant="on">
						<Select
							id="photoSortingColumn"
							class="w-48 border-none"
							v-model="photoSortingColumn"
							:options="photoSortingColumnsOptions"
							optionLabel="label"
							showClear
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
						<label for="photoSortingColumn">{{ $t("lychee.ALBUM_PHOTO_ORDERING") }}</label>
					</FloatLabel>
					<FloatLabel variant="on">
						<Select
							id="photoSortingOrder"
							class="w-48 border-none"
							v-model="photoSortingOrder"
							:options="sortingOrdersOptions"
							optionLabel="label"
							showClear
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
						<label for="photoSortingOrder">asc/desc</label>
					</FloatLabel>
				</div>
				<template v-if="is_model_album">
					<div class="my-2 h-10 flex">
						<FloatLabel variant="on">
							<Select
								id="albumSortingColumn"
								class="w-48 border-none"
								v-model="albumSortingColumn"
								:options="albumSortingColumnsOptions"
								optionLabel="label"
								showClear
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
							<label for="albumSortingColumn">{{ $t("lychee.ALBUM_CHILDREN_ORDERING") }}</label>
						</FloatLabel>
						<FloatLabel variant="on">
							<Select
								id="albumSortingOrder"
								class="w-48 border-none"
								v-model="albumSortingOrder"
								:options="sortingOrdersOptions"
								optionLabel="label"
								showClear
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
							<label for="albumSortingOrder">asc/desc</label>
						</FloatLabel>
					</div>
					<div class="h-10 my-2">
						<FloatLabel variant="on">
							<Select id="header" class="w-72 border-none" v-model="header_id" :options="headersOptions" optionLabel="title" showClear>
								<template #value="slotProps">
									<div v-if="slotProps.value && slotProps.value.id === 'compact'">
										<i class="pi pi-arrow-down-left-and-arrow-up-right-to-center" />
										<span class="ml-4 text-left">{{ $t("lychee.SET_COMPACT_HEADER") }}</span>
									</div>
									<div v-else-if="slotProps.value" class="flex items-center">
										<img :src="slotProps.value.thumb" alt="poster" class="w-4 rounded-sm" />
										<span class="ml-4 text-left">{{ slotProps.value.title }}</span>
									</div>
								</template>
								<template #option="slotProps">
									<div v-if="slotProps.option.id === 'compact'" class="flex items-center">
										<i class="pi pi-arrow-down-left-and-arrow-up-right-to-center" />
										<span class="ml-4 text-left">{{ $t("lychee.SET_COMPACT_HEADER") }}</span>
									</div>
									<div v-else class="flex items-center">
										<img :src="slotProps.option.thumb" alt="poster" class="w-4 rounded-sm" />
										<span class="ml-4 text-left">{{ slotProps.option.title }}</span>
									</div>
								</template>
							</Select>
							<label for="header">{{ $t("lychee.SET_HEADER") }}</label>
						</FloatLabel>
					</div>
					<!-- <livewire:forms.album.set-header :album_id="$this->albumID" lazy="on-load" /> -->
					<div class="h-10 my-2">
						<FloatLabel variant="on">
							<Select id="license" class="w-72 border-none" v-model="license" :options="licenseOptions" optionLabel="label" showClear>
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
							<label for="license">{{ $t("lychee.ALBUM_SET_LICENSE") }}</label>
						</FloatLabel>
					</div>
					<div class="my-2">
						<FloatLabel variant="on">
							<InputText id="copyright" v-model="copyright" />
							<label for="copyright">{{ $t("lychee.ALBUM_SET_COPYRIGHT") }}</label>
						</FloatLabel>
					</div>
					<div class="h-10 my-2 pt-4">
						<FloatLabel variant="on">
							<Select
								id="aspectRatio"
								class="w-72 border-none"
								v-model="aspectRatio"
								:options="aspectRationOptions"
								optionLabel="label"
								showClear
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
							<label for="aspectRatio">Set album thumbs aspect ratio</label>
						</FloatLabel>
					</div>
				</template>
				<div v-if="!is_model_album" class="mb-8 h-10">
					<FloatLabel variant="on">
						<AutoComplete
							id="tags"
							v-model="tags"
							:typeahead="false"
							multiple
							class="pt-3 border-b hover:border-b-0 w-full"
							pt:inputmultiple:class="w-full border-t-0 border-l-0 border-r-0 border-b hover:border-b-primary-400 focus:border-b-primary-400"
						/>
						<label for="tags">{{ $t("lychee.ALBUM_SET_SHOWTAGS") }}</label>
					</FloatLabel>
				</div>
				<Button class="p-3 mt-4 w-full font-bold border-none text-white hover:bg-primary-500 hover:text-surface-0 flex-shrink" @click="save">
					{{ $t("lychee.SAVE") }}
				</Button>
			</form>
		</template>
	</Card>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
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
	SelectOption,
	SelectBuilders,
} from "@/config/constants";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import AutoComplete from "primevue/autocomplete";

type HeaderOption = {
	id: string;
	title?: string;
	thumb?: string | null;
};

const props = defineProps<{
	editable: App.Http.Resources.Editable.EditableBaseAlbumResource;
	photos: App.Http.Resources.Models.PhotoResource[];
}>();

const toast = useToast();
const is_model_album = ref(true);
const albumId = ref("");
const title = ref("");
const description = ref(null as null | string);
const photoSortingColumn = ref(undefined as SelectOption<App.Enum.ColumnSortingPhotoType> | undefined);
const photoSortingOrder = ref(undefined as SelectOption<App.Enum.OrderSortingType> | undefined);
const albumSortingColumn = ref(undefined as SelectOption<App.Enum.ColumnSortingAlbumType> | undefined);
const albumSortingOrder = ref(undefined as SelectOption<App.Enum.OrderSortingType> | undefined);
const license = ref(undefined as SelectOption<App.Enum.LicenseType> | undefined);
const copyright = ref(undefined as undefined | string);
const tags = ref([] as string[]);
const aspectRatio = ref(undefined as SelectOption<App.Enum.AspectRatioType> | undefined);
const header_id = ref(undefined as HeaderOption | undefined);

const headersOptions = computed(() => {
	const list: HeaderOption[] = [
		{
			id: "compact",
			title: trans("lychee.SET_COMPACT_HEADER"),
		},
	];
	list.push(
		...props.photos.map((photo) => ({
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
	description.value = editable.description;
	photoSortingColumn.value = SelectBuilders.buildPhotoSorting(editable.photo_sorting?.column);
	photoSortingOrder.value = SelectBuilders.buildSortingOrder(editable.photo_sorting?.order);
	albumSortingColumn.value = SelectBuilders.buildAlbumSorting(editable.album_sorting?.column);
	albumSortingOrder.value = SelectBuilders.buildSortingOrder(editable.album_sorting?.order);
	license.value = SelectBuilders.buildLicense(editable.license);
	aspectRatio.value = SelectBuilders.buildAspectRatio(editable.aspect_ratio);
	header_id.value = buildHeaderId(editable.header_id, photos);
	tags.value = editable.tags;
}

load(props.editable, props.photos);

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
	};
	AlbumService.updateAlbum(data)
		.then(() => {
			toast.add({ severity: "success", summary: "Success", life: 3000 });
			AlbumService.clearCache(albumId.value);
		})
		.catch((error) => {
			console.error(error);
		});
}

function saveTagAlbum() {
	if (tags.value.length === 0) {
		toast.add({ severity: "error", summary: "Error", detail: "Tags are required", life: 3000 });
		return;
	}

	const data: UpdateTagAlbumData = {
		album_id: albumId.value,
		title: title.value,
		tags: tags.value,
		description: description.value,
		photo_sorting_column: photoSortingColumn.value?.value ?? null,
		photo_sorting_order: photoSortingOrder.value?.value ?? null,
		copyright: copyright.value ?? null,
	};
	AlbumService.updateTag(data)
		.then(() => {
			toast.add({ severity: "success", summary: "Success", life: 3000 });
			AlbumService.clearCache(albumId.value);
		})
		.catch((error) => {
			console.error(error);
		});
}

watch(
	() => [props.editable, props.photos],
	([editable, photos]) => {
		// @ts-expect-error
		load(editable, photos);
	},
);
</script>
