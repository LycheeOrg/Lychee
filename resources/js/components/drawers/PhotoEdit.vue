<template>
	<Drawer :closeOnEsc="false" v-model:visible="isEditOpen" position="right" pt:root:class="w-full p-card border-transparent">
		<Card id="lychee_sidebar" v-if="props.photo" class="h-full pr-4 break-words max-w-4xl mx-auto">
			<template #content>
				<form class="w-full flex flex-col md:gap-y-4 md:grid md:grid-cols-[200px_minmax(auto,_1fr)] justify-center">
					<label for="title" class="font-bold self-center">{{ $t("lychee.PHOTO_SET_TITLE") }}</label>
					<InputText id="title" type="text" v-model="title" :invalid="!title" />

					<label for="description" class="font-bold mt-4 md:mt-0">{{ $t("lychee.PHOTO_SET_DESCRIPTION") }}</label>
					<Textarea id="description" class="w-full h-48" v-model="description" rows="5" cols="30" />

					<label for="tags" class="font-bold mt-4 md:mt-0 self-center">{{ $t("lychee.PHOTO_SET_TAGS") }}</label>
					<AutoComplete id="tags" v-model="tags" multiple :typeahead="false"></AutoComplete>

					<label for="uploadDate" class="font-bold mt-4 md:mt-0 self-center">{{ $t("lychee.PHOTO_SET_CREATED_AT") }}</label>
					<DatePicker
						id="uploadDate"
						v-model="uploadDate"
						:showTime="true"
						hourFormat="24"
						dateFormat=""
						:showSeconds="true"
						:invalid="!uploadDate"
					/>

					<label for="license" class="font-bold mt-4 md:mt-0 self-center">{{ $t("lychee.SET_LICENSE") }}</label>
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
					<Button
						text
						class="w-full col-span-2 font-bold border-none text-primary-500 hover:bg-primary-500 hover:text-surface-0"
						@click="save"
					>
						{{ $t("lychee.SAVE") }}
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

const photo_id = ref(undefined as string | undefined);
const title = ref(undefined as string | undefined);
const description = ref(undefined as string | undefined);
const uploadDate = ref(undefined as Date | undefined);
const tags = ref([] as string[]);
const license = ref(undefined as SelectOption<App.Enum.LicenseType> | undefined);
const uploadTz = ref(undefined as string | undefined);

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
