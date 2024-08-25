<template>
	<Drawer :closeOnEsc="false" v-model:visible="isEditOpen" position="right" pt:root:class="w-full p-card border-transparent">
		<Card id="lychee_sidebar" v-if="props.photo" class="h-full pr-4 break-words max-w-4xl mx-auto">
			<template #content>
				<form class="w-full flex justify-center flex-col">
					<div class="w-full">
						<p class="font-bold">{{ $t("lychee.PHOTO_SET_TITLE") }}</p>
						<p class="text-text-main-400">{{ $t("lychee.PHOTO_NEW_TITLE") }}</p>
						<InputText id="title" type="text" v-model="title" />
					</div>

					<div class="my-4 h-56 pt-4 w-full">
						<p class="font-bold">{{ $t("lychee.PHOTO_SET_DESCRIPTION") }}</p>
						<p class="text-text-main-400">{{ $t("lychee.PHOTO_NEW_DESCRIPTION") }}</p>
						<Textarea id="description" class="w-full h-48" v-model="description" rows="5" cols="30" />
					</div>
					<div class="my-4 w-full">
						<p class="font-bold">{{ $t("lychee.PHOTO_SET_TAGS") }}</p>
						<p class="text-text-main-400">{{ $t("lychee.PHOTO_NEW_TAGS") }}</p>
						<!-- <x-forms.inputs.text x-model="tagsWithComma" /> -->
					</div>
					<div class="my-4 w-full">
						<p class="font-bold">{{ $t("lychee.PHOTO_SET_CREATED_AT") }}</p>
						<p class="text-text-main-400">{{ $t("lychee.PHOTO_NEW_CREATED_AT") }}</p>
						<!-- <x-forms.inputs.date x-model="uploadDate" /> -->
					</div>
					<div class="my-4 w-full">
						<p>
							<span class="font-bold">{{ $t("lychee.SET_LICENSE") }}</span>
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
						</p>
					</div>
					<Button class="p-3 w-full font-bold border-none text-white hover:bg-primary-500 hover:text-surface-0 flex-shrink" @click="save">
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
import { ref, Ref } from "vue";
import InputText from "../forms/basic/InputText.vue";
import { licenseOptions, SelectOption, SelectBuilders } from "@/config/constants";
import Select from "primevue/select";
import Textarea from "../forms/basic/Textarea.vue";

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource;
}>();

const isEditOpen = defineModel("isEditOpen", { default: false }) as Ref<boolean>;

const title = ref(props.photo.title);
const description = ref(props.photo.description);
const license = ref(undefined as SelectOption<App.Enum.LicenseType> | undefined);

function save() {}

function load() {
	license.value = SelectBuilders.buildLicense(props.photo.license);
}

load();
</script>
