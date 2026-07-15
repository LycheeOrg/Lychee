<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<div class="flex flex-col justify-center gap-2 w-full items-center">
				<template v-if="token === undefined">
					<span>{{ tokenText }}</span>
					<a v-if="isDisabled" class="cursor-pointer font-bold text-primary underline" @click="resetToken">
						{{ $t("profile.token.create") }}
					</a>
					<a v-else class="cursor-pointer font-bold text-primary underline" @click="resetToken">
						{{ $t("profile.token.reset") }}
					</a>
				</template>
				<template v-else>
					<span><UIcon name="lucide:triangle-alert" class="text-error ltr:mr-2 rtl:ml-2" />{{ $t("profile.token.warning") }}</span>
					<UInput v-model="token" readonly class="w-full" />
				</template>
			</div>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton class="flex-1 justify-center" color="neutral" variant="soft" @click="close">
					{{ $t("dialogs.button.close") }}
				</UButton>
				<UButton v-if="!isDisabled && token === undefined" class="flex-1 justify-center" color="error" @click="disable">
					{{ $t("profile.token.disable") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { ref, watch } from "vue";
import { trans } from "laravel-vue-i18n";
import AuthService from "@/services/auth-service";
import ProfileService from "@/services/profile-service";
import { useAppToast } from "@/v8/composables/useAppToast";

const visible = defineModel<boolean>();

const isDisabled = ref(true);
const token = ref<string | undefined>(undefined);
const tokenText = ref("");
const toast = useAppToast();

function resetToken() {
	ProfileService.resetToken().then((response) => {
		token.value = response.data.token;
		isDisabled.value = false;
	});
}

function close() {
	// ! We hide this value to prevent the token from being displayed again.
	token.value = undefined;
	visible.value = false;
}

function disable() {
	ProfileService.unsetToken().then(() => {
		toast.add({
			severity: "success",
			summary: trans("profile.token.disabled"),
			life: 3000,
		});
		visible.value = false;
	});
}

watch(visible, () => {
	AuthService.user().then((response) => {
		tokenText.value = response.data.has_token ? trans("profile.token.unavailable") : trans("profile.token.no_data");
		isDisabled.value = !response.data.has_token;
	});
});
</script>
