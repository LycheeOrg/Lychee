<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<div class="flex flex-col items-center">
				<div class="rounded-full bg-orange-500 text-white inline-flex justify-center items-center h-16 w-16 -mt-8">
					<UIcon name="prime:exclamation-triangle" class="text-4xl" />
				</div>
				<div class="pt-4 pb-9 text-center">
					<h2 class="font-bold text-xl">{{ $t("users.invite.links_are_not_revokable") }}</h2>
					<span
						class="font-mono block mb-4 mt-6 overflow-hidden text-base cursor-pointer hover:text-primary-400 w-md whitespace-nowrap"
						@click="copy"
						>{{ link }}</span
					>
					<p class="text-muted">{{ sprintf($t("users.invite.link_is_valid_x_days"), days) }}</p>
				</div>
			</div>
		</template>
		<template #footer>
			<UButton color="neutral" variant="soft" class="w-full justify-center font-bold" @click="closeCallback">
				{{ $t("dialogs.button.close") }}
			</UButton>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import UserManagementService from "@/services/user-management-service";
import { trans } from "laravel-vue-i18n";
import { useAppToast } from "@/v8/composables/useAppToast";
import { sprintf } from "sprintf-js";
import { ref } from "vue";
import { Ref } from "vue";

const visible = defineModel("visible", { default: false }) as Ref<boolean>;
const toast = useAppToast();

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
