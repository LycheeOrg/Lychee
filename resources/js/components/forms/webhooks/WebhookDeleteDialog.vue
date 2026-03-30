<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div>
				<p class="p-9 text-center text-muted-color-emphasis max-w-xl text-wrap">
					{{ $t("webhook.confirm_delete_message", { name: webhook.name }) }}<br /><br />
					<span class="text-muted-color">
						<i class="pi pi-exclamation-triangle ltr:mr-2 rtl:ml-2 text-warning-700" />{{ $t("webhook.delete_warning") }}
					</span>
				</p>
				<div class="flex">
					<Button severity="secondary" class="w-full border-none rounded-none rounded-bl-xl font-bold" @click="closeCallback">
						{{ $t("webhook.cancel") }}
					</Button>
					<Button severity="danger" class="w-full border-none rounded-none rounded-br-xl font-bold" :loading="isDeleting" @click="execute">
						{{ $t("webhook.delete") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import WebhookService from "@/services/webhook-service";
import { ref, watch } from "vue";

const toast = useToast();
const props = defineProps<{
	webhook: App.Http.Resources.Models.WebhookResource;
}>();

const visible = defineModel<boolean>("visible", { default: false });
const emits = defineEmits<{
	deleted: [];
}>();

const webhook = ref<App.Http.Resources.Models.WebhookResource>(props.webhook);
const isDeleting = ref(false);

function execute() {
	isDeleting.value = true;
	WebhookService.delete(webhook.value.id)
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("webhook.deleted"), life: 3000 });
			visible.value = false;
			emits("deleted");
		})
		.catch((err) => {
			console.error("Error deleting webhook:", err);
			toast.add({
				severity: "error",
				summary: trans("toasts.error"),
				detail: trans("webhook.error_delete"),
				life: 3000,
			});
		})
		.finally(() => {
			isDeleting.value = false;
		});
}

watch(
	() => props.webhook,
	(newWebhook) => {
		webhook.value = newWebhook;
	},
);
</script>
