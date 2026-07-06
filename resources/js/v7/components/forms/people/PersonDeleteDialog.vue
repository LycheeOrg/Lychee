<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div>
				<p class="p-9 text-center text-muted-color-emphasis max-w-xl text-wrap">
					{{ sprintf($t("people.person.delete_confirm"), person.name) }}<br /><br />
					<span class="text-muted-color">
						<i class="pi pi-exclamation-triangle ltr:mr-2 rtl:ml-2 text-warning-700" />{{ $t("people.person.delete_warning") }}
					</span>
				</p>
				<div class="flex">
					<Button severity="secondary" class="w-full border-none rounded-none rounded-bl-xl font-bold" @click="closeCallback">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button severity="danger" class="w-full border-none rounded-none rounded-br-xl font-bold" @click="execute">
						{{ $t("people.person.delete") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { sprintf } from "sprintf-js";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import PeopleService from "@/services/people-service";

const toast = useToast();
const props = defineProps<{
	person: App.Http.Resources.Models.PersonResource;
}>();

const visible = defineModel<boolean>("visible", { default: false });
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
