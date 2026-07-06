<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<p class="text-center text-highlighted max-w-xl text-wrap">
				{{ $t("webhook.confirm_delete_message", { name: webhook.name }) }}<br /><br />
				<span class="text-muted flex items-center justify-center gap-1">
					<UIcon name="prime:exclamation-triangle" class="text-warning-700" />{{ $t("webhook.delete_warning") }}
				</span>
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
					{{ $t("webhook.cancel") }}
				</UButton>
				<UButton color="error" class="flex-1 justify-center font-bold" :loading="isDeleting" @click="execute">
					{{ $t("webhook.delete") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import WebhookService from "@/services/webhook-service";
import { ref, watch } from "vue";

const toast = useAppToast();
const props = defineProps<{
	webhook: App.Http.Resources.Models.WebhookResource;
}>();

const visible = defineModel<boolean>("open", { default: false });
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
