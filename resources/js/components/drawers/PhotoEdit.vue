<template>
	<Drawer :closeOnEsc="false" v-model:visible="isEditOpen" position="right" pt:root:class="w-full p-card border-transparent">
		<Card id="lychee_sidebar" v-if="props.photo" class="h-full pr-4 break-words max-w-4xl mx-auto">
			<template #content>
				<form class="w-full flex flex-col md:gap-y-4 md:grid md:grid-cols-[200px_minmax(auto,_1fr)] justify-center">
					<label for="title" class="font-bold self-center">{{ $t("gallery.photo.edit.set_title") }}</label>
					<InputText id="title" type="text" v-model="title" :invalid="!title" />

					<label for="description" class="font-bold mt-4 md:mt-0">{{ $t("gallery.photo.edit.set_description") }}</label>
					<Textarea id="description" class="w-full h-48" v-model="description" rows="5" cols="30" />

					<label for="tags" class="font-bold h-11 mt-4 md:mt-0 self-center">{{ $t("gallery.photo.edit.set_tags") }}</label>
					<AutoComplete
						id="tags"
						v-model="tags"
						:typeahead="false"
						multiple
						class="border-b hover:border-b-0"
						:placeholder="$t('gallery.photo.edit.no_tags')"
						pt:inputmultiple:class="w-full border-t-0 border-l-0 border-r-0 border-b hover:border-b-primary-400 focus:border-b-primary-400"
					/>

					<label for="uploadDate" class="font-bold mt-4 md:mt-0 self-center">{{ $t("gallery.photo.edit.set_created_at") }}</label>
					<DatePicker
						id="uploadDate"
						v-model="uploadDate"
						:showTime="true"
						hourFormat="24"
						dateFormat=""
						:showSeconds="true"
						:invalid="!uploadDate"
						class="border-0 p-0 w-full border-b hover:border-b-primary-400 focus:border-b-primary-400"
					/>

					<label for="license" class="font-bold mt-4 md:mt-0 self-center">{{ $t("gallery.photo.edit.set_license") }}</label>
					<Select
						id="license"
						class="w-72 border-none"
						v-model="license"
						:options="licenseOptions"
						optionLabel="label"
						showClear
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
import { ref, Ref, watch } from "vue";
import InputText from "@/components/forms/basic/InputText.vue";
import { licenseOptions, SelectOption, SelectBuilders } from "@/config/constants";
import Select from "primevue/select";
import Textarea from "@/components/forms/basic/Textarea.vue";
import DatePicker from "primevue/datepicker";
import AutoComplete from "primevue/autocomplete";
import PhotoService from "@/services/photo-service";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource;
}>();

const toast = useToast();
const isEditOpen = defineModel("isEditOpen", { default: false }) as Ref<boolean>;

const photo_id = ref<string | undefined>(undefined);
const title = ref<string | undefined>(undefined);
const description = ref<string | undefined>(undefined);
const uploadDate = ref<Date | undefined>(undefined);
const tags = ref<string[]>([]);
const license = ref<SelectOption<App.Enum.LicenseType> | undefined>(undefined);
const uploadTz = ref<string | undefined>(undefined);

// TODO: updating exif data later

function load(photo: App.Http.Resources.Models.PhotoResource) {
	photo_id.value = photo.id;
	title.value = photo.title;
	description.value = photo.description;
	tags.value = photo.tags;

	const dataDate = (photo.created_at ?? "").slice(0, 16);
	uploadTz.value = (photo.created_at ?? "").slice(16);
	uploadDate.value = new Date(dataDate);

	license.value = SelectBuilders.buildLicense(photo.license);
}

function save() {
	if (!photo_id.value || !title.value || !uploadDate.value) {
		return;
	}

	PhotoService.update(photo_id.value, {
		title: title.value,
		description: description.value ?? "",
		tags: tags.value ?? [],
		license: license.value?.value ?? "none",
		upload_date: uploadDate.value?.toISOString().slice(0, 16) + uploadTz.value,
	})
		.then((response) => {
			toast.add({ severity: "success", summary: "Success", life: 3000 });
			load(response.data);
		})
		.catch((error) => {
			console.error(error);
		});
}

load(props.photo);

watch(
	() => props.photo,
	(newPhoto: App.Http.Resources.Models.PhotoResource, _oldPhoto) => load(newPhoto),
);
</script>
<style lang="css">
/* Only way to get rid of the border sadly. */
.p-datepicker-input {
	border: none;
}
</style>
