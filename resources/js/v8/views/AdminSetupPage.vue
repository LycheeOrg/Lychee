<template>
	<UCard class="p-9 mx-auto max-w-3xl" :ui="{ header: 'hidden', body: 'flex flex-col items-center' }">
		<div class="my-12 flex flex-col items-center gap-3 h-24">
			<Transition name="fade" mode="out-in">
				<p
					:key="welcomeText"
					:dir="welcomeDir"
					class="text-center text-2xl text-highlighted uppercase font-extralight"
					v-html="welcomeText"
				></p>
			</Transition>
		</div>
		<h2 class="text-center text-muted pt-9">
			{{ $t("profile.admin_setup.header") }}
		</h2>
		<UAlert v-if="errorMessage" color="error" variant="soft" class="mb-4 text-center" :description="errorMessage" />
		<form class="flex flex-col gap-4 relative max-w-md w-full text-sm rounded-md pb-9" @submit.prevent="submit">
			<div class="inline-flex flex-col gap-4">
				<UFormField :label="$t('profile.login.username')">
					<UInput id="username" v-model="username" autocomplete="username" :autofocus="true" class="w-full" />
				</UFormField>
				<UFormField :label="$t('profile.login.new_password')">
					<InputPassword id="password" v-model="password" autocomplete="new-password" has-check />
				</UFormField>
				<UFormField :label="$t('profile.login.confirm_new_password')">
					<InputPassword id="password_confirmation" v-model="passwordConfirmation" autocomplete="new-password" />
				</UFormField>
				<UAlert v-if="confirmationError" color="error" variant="soft" class="text-sm mt-2" :description="confirmationError" />
			</div>
			<div class="flex items-center mt-9">
				<UButton
					variant="solid"
					type="submit"
					:disabled="!isFormValid || isSubmitting"
					:loading="isSubmitting"
					color="primary"
					class="w-full font-bold justify-center"
				>
					{{ $t("profile.admin_setup.submit") }}
				</UButton>
			</div>
		</form>
		<div class="text-muted">
			<UIcon name="lucide:circle-question-mark" class="inline-block text-info ltr:mr-1 rtl:ml-1" />
			<span v-html="$t('profile.login.password_strength_info')"></span>
		</div>
	</UCard>
</template>

<script setup lang="ts">
import InputPassword from "@/v8/components/forms/basic/InputPassword.vue";
import AdminSetupService from "@/services/admin-setup-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useAdminWelcomeRotator } from "@/v8/composables/useAdminWelcomeRotator";
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import { trans } from "laravel-vue-i18n";

const router = useRouter();
const toast = useAppToast();

const { text: welcomeText, dir: welcomeDir } = useAdminWelcomeRotator();

const username = ref("");
const password = ref("");
const passwordConfirmation = ref("");
const errorMessage = ref("");
const isSubmitting = ref(false);

const confirmationError = computed(() => {
	return password.value !== passwordConfirmation.value ? trans("profile.register.password_mismatch") : "";
});

const isFormValid = computed(() => {
	return username.value !== "" && password.value !== "" && passwordConfirmation.value !== "" && confirmationError.value === "";
});

function submit() {
	if (isSubmitting.value) {
		return;
	}
	isSubmitting.value = true;
	AdminSetupService.create({
		username: username.value,
		password: password.value,
		password_confirmation: passwordConfirmation.value,
	})
		.then(() => {
			errorMessage.value = "";
			toast.add({ severity: "success", summary: trans("profile.admin_setup.success"), life: 3000 });
			router.push({ name: "gallery" });
		})
		.catch((error) => {
			if (error.response?.status === 403) {
				errorMessage.value = trans("profile.admin_setup.already_exists");
			} else if (error.response?.status === 409) {
				errorMessage.value = trans("profile.register.username_exists");
			} else {
				errorMessage.value = error.response?.data?.message || trans("profile.admin_setup.error");
			}
		})
		.finally(() => {
			isSubmitting.value = false;
		});
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
	transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
	opacity: 0;
}
</style>
