<template>
	<Drawer v-model:visible="isEditOpen" :close-on-esc="false" position="right" pt:root:class="w-full p-card border-transparent">
		<Card v-if="photo" id="lychee_sidebar" class="h-full pr-4 wrap-break-word max-w-4xl mx-auto">
			<template #content>
				<form class="w-full flex flex-col md:gap-y-4 md:grid md:grid-cols-[200px_minmax(auto,1fr)] justify-center">
					<label for="title" class="font-bold self-center">{{ $t("gallery.photo.edit.set_title") }}</label>
					<InputText id="title" v-model="title" type="text" :invalid="!title" />

					<label for="description" class="font-bold mt-4 md:mt-0">{{ $t("gallery.photo.edit.set_description") }}</label>
					<Textarea id="description" v-model="description" class="w-full h-48" :rows="5" :cols="30" />

					<label for="tags" class="font-bold h-11 mt-4 md:mt-0 self-center">{{ $t("gallery.photo.edit.set_tags") }}</label>
					<TagsInput id="tags" v-model="tags" :add="true" :placeholder="$t('gallery.photo.edit.no_tags')" />
					<label for="uploadDate" class="font-bold mt-4 md:mt-0 self-center">{{ $t("gallery.photo.edit.set_created_at") }}</label>
					<DatePicker
						id="uploadDate"
						v-model="uploadDate"
						:show-time="true"
						hour-format="24"
						date-format=""
						:show-seconds="true"
						:invalid="!uploadDate"
						updateModelType="date"
						class="border-0 p-0 w-full border-b hover:border-b-primary-400 focus:border-b-primary-400"
					/>

					<label for="takenAtDate" class="font-bold mt-4 md:mt-0 self-center">{{ $t("gallery.photo.edit.set_taken_at") }}</label>

					<InputGroup>
						<InputGroupAddon class="border-t-0 rounded-t-none">
							<Checkbox v-model="is_taken_at_modified" v-tooltip="'Modify taken date'" :binary="true" />
						</InputGroupAddon>
						<DatePicker
							id="takenAtDate"
							v-model="takenAtDate"
							:show-time="true"
							hour-format="24"
							date-format=""
							:show-seconds="true"
							updateModelType="date"
							:disabled="!is_taken_at_modified"
							:class="{
								'border-0 p-0 w-full border-b hover:border-b-primary-400 focus:border-b-primary-400 ltr:rounded-br-none rtl:rounded-bl-none': true,
								'border-dashed': !is_taken_at_modified,
							}"
						/>
						<InputGroupAddon
							:class="{
								'border-t-0 ltr:border-r-0 rtl:border-l-0 ltr:rounded-br-none rtl:rounded-bl-none rounded-t-none hover:border-b-primary-400': true,
								'border-dashed': !is_taken_at_modified,
							}"
						>
							<Select
								v-model="takenAtTz"
								:options="timeZoneOptions"
								option-label="label"
								option-value="value"
								:disabled="!is_taken_at_modified"
								:invalid="!takenAtTz"
								class="border-none"
							></Select>
						</InputGroupAddon>
					</InputGroup>
					<div></div>
					<div
						class="mt-0 md:-mt-2 text-sm text-muted-color"
						v-html="sprintf($t('gallery.photo.edit.set_taken_at_info'), '<span class=\'text-warning-600\'>*</span>')"
					></div>

					<label for="license" class="font-bold mt-4 md:mt-0 self-center">{{ $t("gallery.photo.edit.set_license") }}</label>
					<Select
						id="license"
						v-model="license"
						class="w-72 border-none"
						:options="licenseOptions"
						option-label="label"
						show-clear
						:invalid="!license"
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
					<Button severity="primary" class="w-full col-span-2 font-bold border-none" @click="save">
						{{ $t("dialogs.button.save") }}
					</Button>
				</form>
			</template>
		</Card>
	</Drawer>
</template>
<script setup lang="ts">
import Card from "primevue/card";
import Drawer from "primevue/drawer";
import { onMounted, ref, Ref, watch } from "vue";
import InputText from "@/components/forms/basic/InputText.vue";
import { licenseOptions, SelectOption, SelectBuilders, timeZoneOptions } from "@/config/constants";
import Select from "primevue/select";
import Textarea from "@/components/forms/basic/Textarea.vue";
import DatePicker from "primevue/datepicker";
import PhotoService from "@/services/photo-service";
import Button from "primevue/button";
import InputGroup from "primevue/inputgroup";
import InputGroupAddon from "primevue/inputgroupaddon";
import { useToast } from "primevue/usetoast";
import Checkbox from "primevue/checkbox";
import { sprintf } from "sprintf-js";
import { useRouter } from "vue-router";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import TagsInput from "@/components/forms/basic/TagsInput.vue";
import TagsService from "@/services/tags-service";
import AlbumService from "@/services/album-service";
import { usePhotoStore } from "@/stores/PhotoState";
import { storeToRefs } from "pinia";

const photoStore = usePhotoStore();

const { photo } = storeToRefs(photoStore);

const toast = useToast();
const router = useRouter();
const { getParentId } = usePhotoRoute(router);
const isEditOpen = defineModel("isEditOpen", { default: false }) as Ref<boolean>;

const photo_id = ref<string | undefined>(undefined);
const title = ref<string | undefined>(undefined);
const description = ref<string | undefined>(undefined);
const uploadDate = ref<Date | undefined>(undefined);
const takenAtDate = ref<Date | undefined>(undefined);
const tags = ref<string[]>([]);
const is_taken_at_modified = ref<boolean>(false);
const license = ref<SelectOption<App.Enum.LicenseType> | undefined>(undefined);
const uploadTz = ref<string | undefined>(undefined);
const takenAtTz = ref<string | undefined>(undefined);

function load(photoToEdit: App.Http.Resources.Models.PhotoResource) {
	photo_id.value = photoToEdit.id;
	title.value = photoToEdit.title;
	description.value = photoToEdit.description;
	tags.value = photoToEdit.tags;

	const dataDate = (photoToEdit.created_at ?? "").slice(0, 19);
	uploadTz.value = (photoToEdit.created_at ?? "").slice(19);
	uploadDate.value = new Date(dataDate);
	is_taken_at_modified.value = photoToEdit.precomputed.is_taken_at_modified;

	if (photoToEdit.taken_at === null) {
		takenAtDate.value = undefined;
		takenAtTz.value = undefined;
	} else {
		const takenDate = (photoToEdit.taken_at ?? "").slice(0, 19);
		takenAtTz.value = (photoToEdit.taken_at ?? "").slice(19);
		takenAtDate.value = new Date(takenDate);
	}

	license.value = SelectBuilders.buildLicense(photoToEdit.license);
}

function save() {
	if (!photo_id.value || !title.value || !uploadDate.value) {
		return;
	}

	let takenDate = null;
	if (takenAtDate.value !== undefined) {
		takenDate = takenAtDate.value.toISOString().slice(0, 19) + (takenAtTz.value ?? "");
	}

	PhotoService.update(photo_id.value, getParentId() ?? null, {
		title: title.value,
		description: description.value ?? "",
		tags: tags.value ?? [],
		license: license.value?.value ?? "none",
		upload_date: uploadDate.value?.toISOString().slice(0, 19) + uploadTz.value,
		taken_at: is_taken_at_modified.value ? takenDate : null,
	}).then((response) => {
		toast.add({ severity: "success", summary: "Success", life: 3000 });
		// Clear cache of tags just in case we added any.
		TagsService.clearCache();
		// Update the parent album cache.
		// This is needed to ensure that the album view is updated with the new photo data
		// and that the tags input is updated with the new tags.
		AlbumService.clearCache(getParentId());
		load(response.data);
	});
}

onMounted(() => {
	if (photoStore.photo) {
		load(photoStore.photo);
	}
});

watch(
	() => photo.value,
	(newPhoto: App.Http.Resources.Models.PhotoResource | undefined, _oldPhoto) => {
		if (newPhoto) {
			load(newPhoto);
		} else {
			photo_id.value = undefined;
		}
	},
);
</script>
<style lang="css">
/* Only way to get rid of the border sadly. */
.p-datepicker-input {
	border: none;
}
</style>
