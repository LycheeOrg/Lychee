<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<p class="text-center text-highlighted max-w-xl text-wrap">
				{{ $t("people.remove_from_person_confirm", { name: personName }) }}
			</p>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton
					color="neutral"
					variant="soft"
					class="flex-1 justify-center font-bold"
					@click="
						() => {
							visible = false;
						}
					"
				>
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="error" variant="solid" class="flex-1 justify-center font-bold" :loading="loading" @click="execute">
					{{ $t("people.remove_from_person") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { ref } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import FaceBatchService from "@/services/face-batch-service";

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource;
	personId: string;
	personName: string;
}>();

const visible = defineModel<boolean>("open", { default: false });
const emits = defineEmits<{
	removed: [affectedCount: number];
}>();

const toast = useAppToast();
const loading = ref(false);

function execute() {
	loading.value = true;
	FaceBatchService.batchUnassignByPhotos([props.photo.id], props.personId)
		.then((data) => {
			visible.value = false;
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.remove_from_person_success"), life: 3000 });
			emits("removed", data.affected_count);
		})
		.catch((e: { response?: { data?: { message?: string } } }) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			loading.value = false;
		});
}
</script>
