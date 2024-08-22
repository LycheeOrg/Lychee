<template>
	<Card class="max-w-xl mx-auto" v-if="user">
		<template #content>
			<form>
				<div class="w-full mt-2">
					<div class="py-5">
						{{ $t("lychee.PASSWORD_TITLE") }}
					</div>
					<FloatLabel>
						<InputPassword id="oldPassword" v-model="oldPassword" :invalid="!oldPassword && hasChanged" />
						<label class="" for="oldPassword">{{ $t("lychee.PASSWORD_CURRENT") }}</label>
					</FloatLabel>
				</div>
				<div class="w-full mt-2">
					<div class="py-5">
						{{ $t("lychee.PASSWORD_TEXT") }}
					</div>
					<FloatLabel>
						<InputText id="username" v-model="username" />
						<label class="" for="username">{{ $t("lychee.USERNAME") }}</label>
					</FloatLabel>
					<FloatLabel class="mt-4">
						<InputPassword id="password" v-model="password" />
						<label class="" for="password">{{ $t("lychee.LOGIN_PASSWORD") }}</label>
					</FloatLabel>
					<FloatLabel class="mt-4">
						<InputPassword id="password_confirmation" v-model="password_confirmation" :invalid="password !== password_confirmation" />
						<label class="" for="password_confirmation">{{ $t("lychee.LOGIN_PASSWORD_CONFIRM") }}</label>
					</FloatLabel>
				</div>
				<div class="w-full mt-2">
					<div class="py-5">
						{{ $t("lychee.USER_EMAIL_INSTRUCTION") }}
					</div>
					<FloatLabel>
						<InputText id="email" v-model="email" />
						<label class="" for="email">{{ $t("lychee.ENTER_EMAIL") }}</label>
					</FloatLabel>
				</div>
				<div class="flex w-full mt-4">
					<Button
						class="p-3 w-full font-bold border-none text-primary-500 bg-surface hover:bg-primary-500 hover:text-surface-0 flex-shrink"
						@click="save"
					>
						{{ $t("lychee.PASSWORD_CHANGE") }}
					</Button>
					<Button
						class="p-3 w-full font-bold border-none text-primary-500 bg-surface hover:bg-primary-500 hover:text-surface-0 flex-shrink"
						@click="isApiTokenOpen = !isApiTokenOpen"
					>
						{{ $t("lychee.TOKEN_BUTTON") }}
					</Button>
				</div>
			</form>
		</template>
	</Card>
	<ApiToken v-model="isApiTokenOpen" />
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import Card from "primevue/card";
import FloatLabel from "primevue/floatlabel";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
import InputPassword from "@/components/forms/basic/InputPassword.vue";
import InputText from "@/components/forms/basic/InputText.vue";
import ApiToken from "@/components/forms/profile/ApiToken.vue";
import ProfileService from "@/services/profile-service";
import AuthService from "@/services/auth-service";

const isApiTokenOpen = ref(false);

const user = ref(undefined as undefined | App.Http.Resources.Models.UserResource);
const oldPassword = ref(undefined as undefined | string);
const username = ref(undefined as undefined | string);
const password = ref(undefined as undefined | string);
const password_confirmation = ref(undefined as undefined | string);
const email = ref(undefined as undefined | string);

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
			summary: "Missing fields",
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
	}).then((data) => {
		oldPassword.value = undefined;
		username.value = data.data.username as string;
		password.value = undefined;
		password_confirmation.value = undefined;
		email.value = data.data.email ?? undefined;
	});
}

load();
</script>
