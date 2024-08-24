<template>
	<Dialog
		v-model:visible="visible"
		modal
		:pt="{
			root: 'border-none',
			mask: {
				style: 'backdrop-filter: blur(2px)',
			},
		}"
	>
		<template #container="{ closeCallback }">
			<div v-focustrap class="flex flex-col gap-4 relative max-w-full text-sm rounded-md pt-9">
				<div class="inline-flex flex-col gap-2 px-9">
					<FloatLabel>
						<InputText id="username" v-model="username" />
						<label class="" for="username">{{ $t("lychee.USERNAME") }}</label>
					</FloatLabel>
				</div>
				<div class="inline-flex flex-col gap-2 px-9">
					<FloatLabel>
						<InputPassword id="password" v-model="password" @keydown.enter="login" />
						<label class="" for="password">{{ $t("lychee.PASSWORD") }}</label>
					</FloatLabel>
					<Message v-if="invalidPassword" severity="error">Unknown user or invalid password</Message>
				</div>
				<div class="flex items-center mt-9">
					<Button
						@click="closeCallback"
						text
						class="p-3 w-full font-bold border-none text-muted-color hover:text-danger-700 rounded-bl-xl flex-shrink-2"
						>{{ trans("lychee.CANCEL") }}</Button
					>
					<Button
						@click="login"
						text
						class="p-3 w-full font-bold border-none text-primary-500 hover:bg-primary-500 hover:text-surface-0 rounded-none rounded-br-xl flex-shrink"
						>{{ trans("lychee.SIGN_IN") }}</Button
					>
				</div>
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { Ref, ref, watch } from "vue";
import FloatLabel from "primevue/floatlabel";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Message from "primevue/message";
import { trans } from "laravel-vue-i18n";
import AuthService from "@/services/auth-service";
import InputText from "@/components/forms/basic/InputText.vue";
import InputPassword from "@/components/forms/basic/InputPassword.vue";
import { useAuthStore } from "@/stores/Auth";

const visible = defineModel("visible", { default: false }) as Ref<boolean>;

const emits = defineEmits<{
	(e: "logged-in"): void;
}>();

const username = ref("");
const password = ref("");
const authStore = useAuthStore();
const invalidPassword = ref(false);

function login() {
	AuthService.login(username.value, password.value)
		.then(() => {
			visible.value = false;
			authStore.setUser(null);
			invalidPassword.value = false;
			emits("logged-in");
		})
		.catch((e: any) => {
			if (e.response && e.response.status === 401) {
				invalidPassword.value = true;
			}
		});
}
</script>
