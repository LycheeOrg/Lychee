<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<p class="text-center text-highlighted max-w-xl text-wrap">
				{{ sprintf($t("people.person.delete_confirm"), person.name) }}<br /><br />
				<span class="text-muted flex items-center justify-center gap-1">
					<UIcon name="lucide:triangle-alert" class="text-warning-700" />{{ $t("people.person.delete_warning") }}
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
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="error" class="flex-1 justify-center font-bold" @click="execute">
					{{ $t("people.person.delete") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { sprintf } from "sprintf-js";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import PeopleService from "@/services/people-service";

const toast = useAppToast();
const props = defineProps<{
	person: App.Http.Resources.Models.PersonResource;
}>();

const visible = defineModel<boolean>("open", { default: false });
const emits = defineEmits<{
	deleted: [];
}>();

function execute() {
	visible.value = false;
	PeopleService.destroy(props.person.id)
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
			emits("deleted");
		})
		.catch((err) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: err.response?.data?.message, life: 3000 });
		});
}
</script>
