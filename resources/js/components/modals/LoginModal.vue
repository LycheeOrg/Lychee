<template>
	<Dialog v-model:visible="visible" modal pt:root:class="border-none" pt:mask:style="backdrop-filter: blur(2px)">
		<template #container="{ closeCallback }">
			<form v-focustrap class="flex flex-col gap-4 relative max-w-full text-sm rounded-md pt-9">
				<div class="text-center">
					<a
						class="inline-block text-xl text-muted-color transition-all duration-300 hover:text-primary-400 hover:scale-150 cursor-pointer"
						@click="openWebAuthn"
					>
						<i class="fa-solid fa-key" />
					</a>
				</div>
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
					<Button @click="closeCallback" severity="secondary" class="w-full font-bold border-none rounded-none rounded-bl-xl flex-shrink-2">
						{{ $t("lychee.CANCEL") }}
					</Button>
					<Button @click="login" severity="contrast" class="w-full font-bold border-none rounded-none rounded-br-xl flex-shrink">
						{{ $t("lychee.SIGN_IN") }}
					</Button>
				</div>
			</form>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { Ref, ref } from "vue";
import FloatLabel from "primevue/floatlabel";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Message from "primevue/message";
import AuthService from "@/services/auth-service";
import InputText from "@/components/forms/basic/InputText.vue";
import InputPassword from "@/components/forms/basic/InputPassword.vue";
import { useAuthStore } from "@/stores/Auth";
import AlbumService from "@/services/album-service";

const visible = defineModel("visible", { default: false }) as Ref<boolean>;

const emits = defineEmits<{
	(e: "logged-in"): void;
	(e: "open-webauthn"): void;
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
			AlbumService.clearCache();
			emits("logged-in");
		})
		.catch((e: any) => {
			if (e.response && e.response.status === 401) {
				invalidPassword.value = true;
			}
		});
}

function openWebAuthn() {
	visible.value = false;
	username.value = "";
	password.value = "";
	invalidPassword.value = false;
	emits("open-webauthn");
}
</script>
