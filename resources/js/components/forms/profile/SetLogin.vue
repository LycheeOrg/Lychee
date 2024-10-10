<template>
	<Fieldset
		:legend="loginTitle"
		:toggleable="true"
		class="border-b-0 border-r-0 rounded-r-none rounded-b-none mb-4 hover:border-primary-500 pt-2 max-w-xl mx-auto"
		:pt:legendlabel:class="'capitalize'"
		v-if="user && user.id !== null"
	>
		<form>
			<div class="w-full">
				<div class="pb-5">
					{{ $t("lychee.PASSWORD_TITLE") }}
				</div>
				<FloatLabel variant="on">
					<InputPassword id="oldPassword" v-model="oldPassword" :invalid="!oldPassword && hasChanged" />
					<label class="" for="oldPassword">{{ $t("lychee.PASSWORD_CURRENT") }}</label>
				</FloatLabel>
			</div>
			<div class="w-full mt-2">
				<div class="py-5">
					{{ $t("lychee.PASSWORD_TEXT") }}
				</div>
				<FloatLabel variant="on">
					<InputText id="username" v-model="username" />
					<label class="" for="username">{{ $t("lychee.USERNAME") }}</label>
				</FloatLabel>
				<FloatLabel class="mt-4" variant="on">
					<InputPassword id="password" v-model="password" />
					<label class="" for="password">{{ $t("lychee.LOGIN_PASSWORD") }}</label>
				</FloatLabel>
				<FloatLabel class="mt-4" variant="on">
					<InputPassword id="password_confirmation" v-model="password_confirmation" :invalid="password !== password_confirmation" />
					<label class="" for="password_confirmation">{{ $t("lychee.LOGIN_PASSWORD_CONFIRM") }}</label>
				</FloatLabel>
			</div>
			<div class="w-full mt-2">
				<div class="py-5">
					{{ $t("lychee.USER_EMAIL_INSTRUCTION") }}
				</div>
				<FloatLabel variant="on">
					<InputText id="email" v-model="email" />
					<label class="" for="email">{{ $t("lychee.ENTER_EMAIL") }}</label>
				</FloatLabel>
			</div>
			<div class="flex w-full mt-4">
				<Button severity="contrast" class="w-full font-bold border-none flex-shrink rounded-none rounded-bl-xl rounded-tl-xl" @click="save">
					{{ $t("lychee.PASSWORD_CHANGE") }}
				</Button>
				<Button
					severity="secondary"
					class="w-full font-bold border-none flex-shrink rounded-none rounded-br-xl rounded-tr-xl"
					@click="isApiTokenOpen = !isApiTokenOpen"
				>
					{{ $t("lychee.TOKEN_BUTTON") }}
				</Button>
			</div>
		</form>
	</Fieldset>
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
import Fieldset from "primevue/fieldset";
import { trans } from "laravel-vue-i18n";

const isApiTokenOpen = ref(false);

const user = ref(undefined as undefined | App.Http.Resources.Models.UserResource);
const oldPassword = ref(undefined as undefined | string);
const username = ref(undefined as undefined | string);
const password = ref(undefined as undefined | string);
const password_confirmation = ref(undefined as undefined | string);
const email = ref(undefined as undefined | string);

const loginTitle = computed(() => trans("lychee.PROFILE"));

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
		toast.add({
			severity: "success",
			summary: "Success",
			life: 3000,
		});
	});
}

load();
</script>
