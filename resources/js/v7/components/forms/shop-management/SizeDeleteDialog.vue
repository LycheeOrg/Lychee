<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div>
				<p class="p-9 text-center text-muted-color-emphasis max-w-xl text-wrap">
					{{ $t("webshop.sizeCatalogue.confirmDeleteMessage") }}<br /><br />
					<span class="text-muted-color">
						<i class="pi pi-exclamation-triangle ltr:mr-2 rtl:ml-2 text-warning-700" />{{
							$t("webshop.sizeCatalogue.confirmDeleteHeader")
						}}
					</span>
				</p>
				<div class="flex">
					<Button severity="secondary" class="w-full border-none rounded-none rounded-bl-xl font-bold" @click="closeCallback">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button severity="danger" class="w-full border-none rounded-none rounded-br-xl font-bold" :loading="isDeleting" @click="execute">
						{{ $t("dialogs.button.delete") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import ShopManagementService from "@/services/shop-management-service";

const props = defineProps<{
	type: "print" | "pixel";
	deletingSize: App.Http.Resources.Shop.PrintSizeResource | App.Http.Resources.Shop.PixelSizeResource | null;
}>();

const visible = defineModel<boolean>("visible", { default: false });
const emits = defineEmits<{ deleted: [] }>();

const toast = useToast();
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
