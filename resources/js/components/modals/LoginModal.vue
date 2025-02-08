<template>
	<Dialog v-model:visible="is_login_open" modal pt:root:class="border-none" pt:mask:style="backdrop-filter: blur(2px)">
		<template #container="{ closeCallback }">
			<form v-focustrap class="flex flex-col gap-4 relative max-w-full text-sm rounded-md pt-9">
				<div class="flex justify-center gap-2">
					<a
						class="inline-block text-xl text-muted-color transition-all duration-300 hover:text-primary-400 hover:scale-150 cursor-pointer"
						@click="openWebAuthn"
						title="WebAuthn"
					>
						<i class="fa-solid fa-fingerprint" />
					</a>
					<template v-if="oauths !== undefined">
						<a
							v-for="oauth in oauths"
							:href="oauth.url"
							class="inline-block text-xl text-muted-color hover:scale-125 transition-all cursor-pointer hover:text-primary-400 mb-6"
							:title="oauth.provider"
						>
							<i class="items-center" :class="oauth.icon"></i>
						</a>
					</template>
				</div>
				<div class="inline-flex flex-col gap-2 px-9">
					<FloatLabel variant="on">
						<InputText id="username" v-model="username" autocomplete="username" :autofocus="true" />
						<label for="username">{{ $t("dialogs.login.username") }}</label>
					</FloatLabel>
				</div>
				<div class="inline-flex flex-col gap-2 px-9">
					<FloatLabel variant="on">
						<InputPassword id="password" v-model="password" @keydown.enter="login" autocomplete="current-password" />
						<label for="password">{{ $t("dialogs.login.password") }}</label>
					</FloatLabel>
					<Message v-if="invalidPassword" severity="error">{{ $t("dialog.login.unknown_invalid") }}</Message>
				</div>
				<div class="px-9 text-muted-color text-right font-semibold">Lychee <span class="text-primary-500" v-if="is_se_enabled">SE</span></div>
				<div class="flex items-center mt-9">
					<Button @click="closeCallback" severity="secondary" class="w-full font-bold border-none rounded-none rounded-bl-xl shrink">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button @click="login" severity="contrast" class="w-full font-bold border-none rounded-none rounded-br-xl shrink">
						{{ $t("dialogs.login.signin") }}
					</Button>
				</div>
			</form>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { ref } from "vue";
import FloatLabel from "primevue/floatlabel";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Message from "primevue/message";
import AuthService from "@/services/auth-service";
import InputText from "@/components/forms/basic/InputText.vue";
import InputPassword from "@/components/forms/basic/InputPassword.vue";
import { useAuthStore } from "@/stores/Auth";
import AlbumService from "@/services/album-service";
import OauthService from "@/services/oauth-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";

const emits = defineEmits<{
	"logged-in": [];
}>();

type OauthProvider = {
	url: string;
	icon: string;
	provider: App.Enum.OauthProvidersType;
};

const username = ref("");
const password = ref("");
const authStore = useAuthStore();
const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
const { is_se_enabled } = storeToRefs(lycheeStore);
const { is_login_open, is_webauthn_open } = storeToRefs(togglableStore);
const invalidPassword = ref(false);

const oauths = ref<OauthProvider[] | undefined>(undefined);

function login() {
	AuthService.login(username.value, password.value)
		.then(() => {
			is_login_open.value = false;
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

function fetchOauths() {
	OauthService.list()
		.then((res) => {
			oauths.value = (res.data as App.Enum.OauthProvidersType[]).map(mapToOauths);
		})
		.catch((e) => {
			console.error(e);
		});
}

function mapToOauths(provider: App.Enum.OauthProvidersType): OauthProvider {
	let icon = OauthService.providerIcon(provider);
	let url = `/auth/${provider}/authenticate`;
	return { url, icon, provider };
}

fetchOauths();

function openWebAuthn() {
	is_login_open.value = false;
	is_webauthn_open.value = true;
	username.value = "";
	password.value = "";
	invalidPassword.value = false;
}
</script>
