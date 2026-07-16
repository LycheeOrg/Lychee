<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<p class="text-center text-highlighted max-w-xl text-wrap">
				{{ $t("contact.admin.delete_confirm_message", { name: message.name }) }}<br /><br />
				<span class="text-muted flex items-center justify-center gap-1">
					<UIcon name="lucide:triangle-alert" class="text-warning-700" />{{ $t("contact.admin.delete_warning") }}
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
					{{ $t("contact.admin.cancel") }}
				</UButton>
				<UButton color="error" class="flex-1 justify-center font-bold" :loading="isDeleting" @click="execute">
					{{ $t("contact.admin.delete") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import ContactService from "@/services/contact-service";
import { ref, watch } from "vue";

type Message = App.Http.Resources.Models.ContactMessageResource;

const toast = useAppToast();
const props = defineProps<{
	message: Message;
}>();

const visible = defineModel<boolean>("open", { default: false });
const emits = defineEmits<{
	deleted: [];
}>();

const message = ref<Message>(props.message);
const isDeleting = ref(false);

function execute() {
	isDeleting.value = true;
	ContactService.deleteMessage(message.value.id)
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("contact.admin.delete_success"), life: 3000 });
			visible.value = false;
			emits("deleted");
		})
		.catch((err) => {
			console.error("Error deleting contact message:", err);
			toast.add({
				severity: "error",
				summary: trans("toasts.error"),
				detail: trans("contact.admin.delete_error"),
				life: 3000,
			});
		})
		.finally(() => {
			isDeleting.value = false;
		});
}

watch(
	() => props.message,
	(newMessage) => {
		message.value = newMessage;
	},
);
</script>
