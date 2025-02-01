<template>
	<Fieldset
		:legend="$t('profile.login.header')"
		:toggleable="true"
		class="border-b-0 border-r-0 rounded-r-none rounded-b-none mb-4 hover:border-primary-500 pt-2 max-w-xl mx-auto"
		v-if="user && user.id !== null"
	>
		<form>
			<div class="w-full">
				<div class="pb-5">
					{{ $t("profile.login.enter_current_password") }}
				</div>
				<FloatLabel variant="on">
					<InputPassword id="oldPassword" v-model="oldPassword" :invalid="!oldPassword && hasChanged" />
					<label for="oldPassword">{{ $t("profile.login.current_password") }}</label>
				</FloatLabel>
			</div>
			<div class="w-full mt-2">
				<div class="py-5">
					{{ $t("profile.login.credentials_update") }}
				</div>
				<FloatLabel variant="on">
					<InputText id="username" v-model="username" />
					<label for="username">{{ $t("profile.login.username") }}</label>
				</FloatLabel>
				<FloatLabel class="mt-4" variant="on">
					<InputPassword id="password" v-model="password" />
					<label for="password">{{ $t("profile.login.new_password") }}</label>
				</FloatLabel>
				<FloatLabel class="mt-4" variant="on">
					<InputPassword id="password_confirmation" v-model="password_confirmation" :invalid="password !== password_confirmation" />
					<label for="password_confirmation">{{ $t("profile.login.confirm_new_password") }}</label>
				</FloatLabel>
			</div>
			<div class="w-full mt-2">
				<div class="py-5">
					{{ $t("profile.login.email_instruction") }}
				</div>
				<FloatLabel variant="on">
					<InputText id="email" v-model="email" />
					<label for="email">{{ $t("profile.login.email") }}</label>
				</FloatLabel>
			</div>
			<div class="flex w-full mt-4">
				<Button severity="contrast" class="w-full font-bold border-none shrink rounded-none rounded-bl-xl rounded-tl-xl" @click="save">
					{{ $t("profile.login.change") }}
				</Button>
				<Button
					severity="secondary"
					class="w-full font-bold border-none shrink rounded-none rounded-br-xl rounded-tr-xl"
					@click="isApiTokenOpen = !isApiTokenOpen"
				>
					{{ $t("profile.login.api_token") }}
				</Button>
			</div>
		</form>
	</Fieldset>
	<ApiToken v-model="isApiTokenOpen" />
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import FloatLabel from "primevue/floatlabel";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
import InputPassword from "@/components/forms/basic/InputPassword.vue";
import InputText from "@/components/forms/basic/InputText.vue";
import ApiToken from "@/components/forms/profile/ApiToken.vue";
import ProfileService from "@/services/profile-service";
import AuthService from "@/services/auth-service";
import Fieldset from "primevue/fieldset";
import { trans } from "laravel-vue-i18n";

const isApiTokenOpen = ref(false);

const user = ref<App.Http.Resources.Models.UserResource | undefined>(undefined);
const oldPassword = ref<string | undefined>(undefined);
const username = ref<string | undefined>(undefined);
const password = ref<string | undefined>(undefined);
const password_confirmation = ref<string | undefined>(undefined);
const email = ref<string | undefined>(undefined);

const toast = useToast();

const hasChanged = computed(() => {
	return (
		username.value !== user.value?.username ||
		(password.value ?? "") !== "" ||
		(password_confirmation.value ?? "") !== "" ||
		(email.value ?? "") !== (user.value?.email ?? "")
	);
});

function load() {
	AuthService.user().then((data) => {
		user.value = data.data;
		username.value = data.data.username as string;
		email.value = data.data.email ?? undefined;
	});
}

function save() {
	if (hasChanged.value === true && !oldPassword.value) {
		toast.add({
			severity: "error",
			summary: trans("profile.login.missing_fields"),
			life: 3000,
		});
		return;
	}

	ProfileService.update({
		old_password: oldPassword.value as string,
		username: username.value as string,
		password: password.value ?? null,
		password_confirmation: password_confirmation.value ?? null,
		email: email.value ?? null,
	})
		.then((data) => {
			oldPassword.value = undefined;
			username.value = data.data.username as string;
			password.value = undefined;
			password_confirmation.value = undefined;
			email.value = data.data.email ?? undefined;
			toast.add({
				severity: "success",
				summary: trans("toasts.success"),
				life: 3000,
			});
		})
		.catch((e) => {
			toast.add({
				severity: "error",
				summary: trans("toasts.error"),
				detail: e.response.data.message,
				life: 3000,
			});
		});
}

load();
</script>
