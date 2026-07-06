<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<p class="text-sm/8 text-center">
				{{ question }}
			</p>
			<div class="my-3 first:mt-0 last:mb-0">
				<USelectMenu
					v-model="selectedLicense"
					:items="licenseOptions"
					label-key="label"
					:placeholder="$t('dialogs.photo_license.select_license')"
					class="w-full"
				>
					<template #item-label="{ item }">{{ item.label }}</template>
				</USelectMenu>
			</div>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton color="neutral" variant="soft" class="flex-1 justify-center font-bold" @click="close">
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="neutral" class="flex-1 justify-center font-bold" @click="execute">
					{{ $t("dialogs.photo_license.set_license") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { sprintf } from "sprintf-js";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import { licenseOptions } from "@/config/constants";
import type { SelectOption } from "@/config/constants";

const props = defineProps<{
	parentId: string | undefined;
	photo?: App.Http.Resources.Models.PhotoResource;
	photoIds?: string[];
}>();

const visible = defineModel<boolean>("open", { default: false });

const emits = defineEmits<{
	licensed: [];
}>();

const toast = useAppToast();

const question = computed(() => {
	if (props.photo) {
		return trans("dialogs.photo_license.question");
	}
	return sprintf(trans("dialogs.photo_license.question_multiple"), props.photoIds?.length);
});

const selectedLicense = ref<SelectOption<App.Enum.LicenseType> | undefined>(licenseOptions[0]);

function close() {
	visible.value = false;
	selectedLicense.value = licenseOptions[0];
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

	PhotoService.license(photoLicenseIds, selectedLicense.value.value).then(() => {
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
