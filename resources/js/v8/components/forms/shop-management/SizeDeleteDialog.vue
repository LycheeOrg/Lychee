<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<p class="text-center text-highlighted max-w-xl text-wrap">
				{{ $t("webshop.sizeCatalogue.confirmDeleteMessage") }}<br /><br />
				<span class="text-muted">
					<UIcon name="prime:exclamation-triangle" class="ltr:mr-2 rtl:ml-2 text-warning" />{{
						$t("webshop.sizeCatalogue.confirmDeleteHeader")
					}}
				</span>
			</p>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton class="flex-1 justify-center" color="neutral" variant="soft" @click="visible = false">
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton class="flex-1 justify-center" color="error" :loading="isDeleting" @click="execute">
					{{ $t("dialogs.button.delete") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import ShopManagementService from "@/services/shop-management-service";

const props = defineProps<{
	type: "print" | "pixel";
	deletingSize: App.Http.Resources.Shop.PrintSizeResource | App.Http.Resources.Shop.PixelSizeResource | null;
}>();

const visible = defineModel<boolean>("visible", { default: false });
const emits = defineEmits<{ deleted: [] }>();

const toast = useAppToast();
const isDeleting = ref(false);

function execute() {
	if (!props.deletingSize) return;
	isDeleting.value = true;
	const promise =
		props.type === "print"
			? ShopManagementService.deletePrintSize(props.deletingSize.id)
			: ShopManagementService.deletePixelSize(props.deletingSize.id);

	promise
		.then(() => {
			visible.value = false;
			emits("deleted");
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: trans("webshop.sizeCatalogue.error"), detail: error.message, life: 3000 });
		})
		.finally(() => {
			isDeleting.value = false;
		});
}
</script>
