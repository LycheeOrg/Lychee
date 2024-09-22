<template>
	<Card class="sm:p-4 xl:px-9 max-sm:w-full sm:min-w-[32rem] flex-shrink-0">
		<template #content>
			<form>
				<div class="mb-4 h-12">
					<FloatLabel>
						<InputText id="title" type="text" v-model="title" />
						<label for="title">{{ $t("lychee.ALBUM_TITLE") }}</label>
					</FloatLabel>
					<!-- <x-forms.error-message field='title' /> -->
				</div>
				<div class="my-4 h-56 pt-4">
					<FloatLabel>
						<Textarea id="description" class="w-full h-48" v-model="description" rows="5" cols="30" />
						<!-- <x-forms.error-message field='description' /> -->
						<label for="description">{{ $t("lychee.ALBUM_DESCRIPTION") }}</label>
					</FloatLabel>
				</div>
				<div class="my-4 h-10 flex">
					<FloatLabel>
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
					<FloatLabel>
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
					<div class="my-4 h-10 flex">
						<FloatLabel>
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
						<FloatLabel>
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
					<!-- <livewire:forms.album.set-header :album_id="$this->albumID" lazy="on-load" /> -->
					<div class="h-10 my-4">
						<FloatLabel>
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
					<div class="my-4">
						<FloatLabel>
							<InputText id="copyright" v-model="copyright" />
							<label for="copyright">{{ $t("lychee.ALBUM_SET_COPYRIGHT") }}</label>
						</FloatLabel>
						<!-- <x-forms.error-message field='copyright' /> -->
					</div>
					<div class="h-10 my-4">
						<FloatLabel>
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
				<div v-if="!is_model_album" class="mb-4 h-10">
					<FloatLabel>
						<InputChips id="tags" v-model="tags" />
						<label for="tags">{{ $t("lychee.ALBUM_SET_SHOWTAGS") }}</label>
					</FloatLabel>
				</div>
				<Button class="p-3 w-full font-bold border-none text-white hover:bg-primary-500 hover:text-surface-0 flex-shrink" @click="save">
					{{ $t("lychee.SAVE") }}
				</Button>
			</form>
		</template>
	</Card>
</template>
<script setup lang="ts">
import { ref, watch } from "vue";
import Button from "primevue/button";
import Card from "primevue/card";
import Select from "primevue/select";
import FloatLabel from "primevue/floatlabel";
import InputChips from "primevue/inputchips";
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

const props = defineProps<{
	editable: App.Http.Resources.Editable.EditableBaseAlbumResource;
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
const tags = ref(null as null | string);
const aspectRatio = ref(undefined as SelectOption<App.Enum.AspectRatioType> | undefined);

function load(editable: App.Http.Resources.Editable.EditableBaseAlbumResource) {
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
}

load(props.editable);

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
	};
	AlbumService.updateAlbum(data).catch((error) => {
		AlbumService.clearCache(albumId.value);
		console.error(error);
	});
}

function saveTagAlbum() {
	const data: UpdateTagAlbumData = {
		album_id: albumId.value,
		title: title.value,
		tags: tags.value?.split(",") ?? [],
		description: description.value,
		photo_sorting_column: photoSortingColumn.value?.value ?? null,
		photo_sorting_order: photoSortingOrder.value?.value ?? null,
		copyright: copyright.value ?? null,
	};
	AlbumService.updateTag(data)
		.then(() => {
			toast.add({ severity: "success", summary: "Success", detail: "Permission deleted", life: 3000 });
			AlbumService.clearCache(albumId.value);
		})
		.catch((error) => {
			console.error(error);
		});
}

watch(
	() => props.editable,
	(editable) => {
		load(editable);
	},
);
</script>
