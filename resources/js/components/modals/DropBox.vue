<template>
	<Dialog v-model:visible="visible" modal :dismissable-mask="true" pt:root:class="border-none" @hide="closeCallback">
		<template #container="{ closeCallback }">
			<div class="flex flex-col gap-4 bg-gradient-to-b from-bg-300 to-bg-400 relative max-w-full rounded-md text-muted-color">
				<div class="p-9">
					<DropBoxChooser v-if="dropbox_api_key !== 'disabled'" :api-key="dropbox_api_key" @picked="action" @cancel="closeCallback" />
					<p v-else>{{ $t("dialogs.dropbox.not_configured") }}.</p>
				</div>
				<div class="flex justify-center">
					<Button severity="secondary" class="w-full font-bold border-none rounded-none rounded-bl-xl" @click="closeCallback">{{
						$t("dialogs.button.cancel")
					}}</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { Ref } from "vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import PhotoService from "@/services/photo-service";
import DropBoxChooser from "../forms/upload/DropBoxChooser.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const visible = defineModel("visible", { default: false }) as Ref<boolean>;
const props = defineProps<{ albumId: string | null }>();
const emits = defineEmits<{
	refresh: [];
}>();

const lycheeStore = useLycheeStateStore();
const { dropbox_api_key } = storeToRefs(lycheeStore);

function action(files: Dropbox.ChooserFile[]) {
	PhotoService.importFromUrl(
		files.map((file) => file.link),
		props.albumId,
	).then(() => {
		visible.value = false;
		emits("refresh");
	});
}

function closeCallback() {
	visible.value = false;
}
</script>
