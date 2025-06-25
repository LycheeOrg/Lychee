<template>
	<Dialog v-model:visible="visible" modal :dismissable-mask="true" pt:root:class="border-none" @hide="closeCallback">
		<template #container="{ closeCallback }">
			<div class="flex flex-col items-center">
				<div class="rounded-full bg-orange-500 text-primary-contrast inline-flex justify-center items-center h-16 w-16 -mt-8">
					<i class="pi pi-exclamation-triangle text-4xl"></i>
				</div>
				<div class="px-9 pt-4 pb-9 text-center">
					<h2 class="font-bold text-xl">{{ $t("users.invite.links_are_not_revokable") }}</h2>
					<!-- Invitation links are not revokable. -->
					<span
						class="font-mono block mb-4 mt-6 overflow-hidden text-base cursor-pointer hover:text-primary-400 w-md whitespace-nowrap"
						@click="copy"
						>{{ link }}</span
					>
					<p class="text-muted">{{ sprintf($t("users.invite.link_is_valid_x_days"), days) }}</p>
					<!--  This link is valid for {{ days }} days. -->
				</div>
				<Button @click="closeCallback" severity="secondary" class="w-full font-bold border-none rounded-none rounded-bl-xl rounded-br-xl">
					{{ $t("dialogs.button.close") }}
				</Button>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import UserManagementService from "@/services/user-management-service";
import { trans } from "laravel-vue-i18n";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import { useToast } from "primevue/usetoast";
import { sprintf } from "sprintf-js";
import { ref } from "vue";
import { Ref } from "vue";

const visible = defineModel("visible", { default: false }) as Ref<boolean>;
const toast = useToast();

function closeCallback() {
	visible.value = false;
}

const link = ref<string | undefined>(undefined);
const days = ref<number>(0);

UserManagementService.invite().then((response) => {
	link.value = response.data.invitation_link;
	days.value = response.data.valid_for;
});

function copy() {
	if (!link.value) {
		return;
	}

	navigator.clipboard.writeText(link.value).then(() => toast.add({ severity: "success", summary: trans("link copied to clipboard"), life: 3000 }));
}
</script>
