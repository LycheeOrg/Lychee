<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container>
			<div class="p-9 text-center text-muted-color">
				<p class="text-sm/8">
					{{ question }}
				</p>
				<div class="my-3 first:mt-0 last:mb-0">
					<Select
						v-model="selectedLicense"
						:options="licenseOptions"
						option-label="label"
						option-value="value"
						:placeholder="$t('dialogs.photo_license.select_license')"
						class="w-full"
					/>
				</div>
			</div>
			<div class="flex">
				<Button severity="secondary" class="font-bold w-full border-none rounded-none rounded-bl-xl" @click="close">
					{{ $t("dialogs.button.cancel") }}
				</Button>
				<Button severity="contrast" class="font-bold w-full border-none rounded-none rounded-br-xl" @click="execute">
					{{ $t("dialogs.photo_license.set_license") }}
				</Button>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { sprintf } from "sprintf-js";
import { useToast } from "primevue/usetoast";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Select from "primevue/select";
import { trans } from "laravel-vue-i18n";
import { licenseOptions } from "@/config/constants";

const props = defineProps<{
	parentId: string | undefined;
	photo?: App.Http.Resources.Models.PhotoResource;
	photoIds?: string[];
}>();

const visible = defineModel<boolean>("visible", { default: false });

const emits = defineEmits<{
	licensed: [];
}>();

const toast = useToast();

const question = computed(() => {
	if (props.photo) {
		return trans("dialogs.photo_license.question");
	}
	return sprintf(trans("dialogs.photo_license.question_multiple"), props.photoIds?.length);
});

const selectedLicense = ref<App.Enum.LicenseType>("none");

function close() {
	visible.value = false;
	selectedLicense.value = "none";
}

function execute() {
	if (selectedLicense.value === undefined) {
		return;
	}

	let photoLicenseIds = [];
	if (props.photo) {
		photoLicenseIds.push(props.photo.id);
	} else {
		photoLicenseIds = props.photoIds as string[];
	}

	PhotoService.license(photoLicenseIds, selectedLicense.value).then(() => {
		toast.add({
			severity: "success",
			summary: trans("dialogs.photo_license.updated"),
			life: 3000,
		});
		AlbumService.clearCache(props.parentId);
		close();
		emits("licensed");
	});
}
</script>
