<template>
	<Fieldset
		v-if="user && user.id !== null"
		:legend="$t('profile.login.header')"
		:toggleable="true"
		class="mb-4 hover:border-primary pt-2 max-w-xl mx-auto"
	>
		<form>
			<div v-if="user.is_ldap" class="w-full p-4 text-muted">
				<UIcon name="prime:info-circle" class="ltr:mr-2 rtl:ml-2" />
				<span>{{ $t("profile.login.ldap_managed") }}</span>
				<UButton
					color="neutral"
					variant="soft"
					class="w-full font-bold shrink rounded-xl mt-4 justify-center"
					@click="
						() => {
							isApiTokenOpen = !isApiTokenOpen;
						}
					"
				>
					{{ $t("profile.login.api_token") }}
				</UButton>
			</div>
			<template v-else>
				<div v-if="is_basic_auth_enabled" class="w-full mb-6">
					<div class="pb-4">
						{{ $t("profile.login.enter_current_password") }}
					</div>
					<UFormField :label="$t('profile.login.current_password')">
						<InputPassword id="oldPassword" v-model="oldPassword" :invalid="!oldPassword && hasChanged" />
					</UFormField>
				</div>
				<div v-if="is_basic_auth_enabled" class="w-full mb-6">
					<div class="pb-4">
						{{ $t("profile.login.credentials_update") }}
					</div>
					<UFormField :label="$t('profile.login.username')">
						<InputText id="username" v-model="username" />
					</UFormField>
					<UFormField class="mt-4" :label="$t('profile.login.new_password')">
						<InputPassword id="password" v-model="password" />
					</UFormField>
					<UFormField class="mt-4" :label="$t('profile.login.confirm_new_password')">
						<InputPassword id="password_confirmation" v-model="password_confirmation" :invalid="password !== password_confirmation" />
					</UFormField>
				</div>
				<div class="w-full">
					<div class="pb-4">
						{{ $t("profile.login.email_instruction") }}
					</div>
					<UFormField :label="$t('profile.login.email')">
						<InputText id="email" v-model="email" />
					</UFormField>
				</div>
				<div class="flex w-full mt-4">
					<UButton
						color="neutral"
						class="w-full font-bold shrink rounded-none ltr:rounded-l-xl rtl:rounded-r-xl justify-center"
						@click="save"
					>
						{{ $t("profile.login.change") }}
					</UButton>
					<UButton
						color="neutral"
						variant="soft"
						class="w-full font-bold shrink rounded-none ltr:rounded-r-xl rtl:rounded-l-xl justify-center"
						@click="
							() => {
								isApiTokenOpen = !isApiTokenOpen;
							}
						"
					>
						{{ $t("profile.login.api_token") }}
					</UButton>
				</div>
			</template>
		</form>
	</Fieldset>
	<ApiToken v-model="isApiTokenOpen" />
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import InputPassword from "@/v8/components/forms/basic/InputPassword.vue";
import InputText from "@/v8/components/forms/basic/InputText.vue";
import ApiToken from "@/v8/components/forms/profile/ApiToken.vue";
import ProfileService from "@/services/profile-service";
import AuthService from "@/services/auth-service";
import { trans } from "laravel-vue-i18n";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const isApiTokenOpen = ref(false);

const lycheeStore = useLycheeStateStore();
const { is_basic_auth_enabled } = storeToRefs(lycheeStore);

const user = ref<App.Http.Resources.Models.UserResource | undefined>(undefined);
const oldPassword = ref<string | undefined>(undefined);
const username = ref<string | undefined>(undefined);
const password = ref<string | undefined>(undefined);
const password_confirmation = ref<string | undefined>(undefined);
const email = ref<string | undefined>(undefined);

const toast = useAppToast();

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
	if (hasChanged.value === true && !oldPassword.value && is_basic_auth_enabled.value) {
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
