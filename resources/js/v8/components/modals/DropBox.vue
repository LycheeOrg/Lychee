<template>
	<UModal v-model:open="open" :dismissible="true">
		<template #body>
			<DropBoxChooser v-if="dropbox_api_key !== 'disabled'" :api-key="dropbox_api_key" @picked="action" @cancel="closeCallback" />
			<p v-else>{{ $t("dialogs.dropbox.not_configured") }}.</p>
		</template>
		<template #footer>
			<UButton color="neutral" variant="soft" class="w-full justify-center font-bold" @click="closeCallback">
				{{ $t("dialogs.button.cancel") }}
			</UButton>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import PhotoService from "@/services/photo-service";
import DropBoxChooser from "@/v8/components/forms/upload/DropBoxChooser.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import { usePhotoRoute } from "@/composables/photo/photoRoute";

const open = defineModel<boolean>("open", { default: false });
const emits = defineEmits<{
	refresh: [];
}>();

const router = useRouter();
const { getParentId } = usePhotoRoute(router);

const lycheeStore = useLycheeStateStore();
const { dropbox_api_key } = storeToRefs(lycheeStore);

function action(files: Dropbox.ChooserFile[]) {
	PhotoService.importFromUrl(
		files.map((file) => file.link),
		getParentId() ?? null,
	).then(() => {
		open.value = false;
		emits("refresh");
	});
}

function closeCallback() {
	open.value = false;
}
</script>
