<template>
	<form v-if="oauths !== undefined" v-focustrap class="flex flex-col gap-4 relative max-w-md w-full text-sm rounded-md pt-9">
		<div
			:class="{
				'flex justify-center gap-2 w-full': true,
				'flex-col px-9': !is_basic_auth_enabled,
				'flex-row': is_basic_auth_enabled,
			}"
		>
			<a
				v-if="is_webauthn_enabled"
				:class="{
					'inline-block text-xl text-muted-color transition-all duration-300 hover:text-primary-400 cursor-pointer': true,
					'hover:scale-150': is_basic_auth_enabled,
					'hover:scale-105': !is_basic_auth_enabled,
				}"
				title="WebAuthn"
				@click="openWebAuthn"
			>
				<i class="fa-solid fa-fingerprint" />
				<span v-if="!is_basic_auth_enabled" class="ml-2 text-base">{{ sprintf(trans("dialogs.login.auth_with"), "WebAuthn") }}</span>
			</a>
			<a
				v-for="oauth in oauths"
				:key="oauth.provider"
				:href="oauth.url"
				:class="{
					'inline-block text-xl text-muted-color transition-all duration-300 hover:text-primary-400 cursor-pointer': true,
					'hover:scale-150': is_basic_auth_enabled,
					'hover:scale-105': !is_basic_auth_enabled,
				}"
				:title="oauth.provider"
			>
				<i class="items-center" :class="oauth.icon"></i>
				<span v-if="!is_basic_auth_enabled" class="ml-2 text-base">{{ sprintf(trans("dialogs.login.auth_with"), oauth.provider) }}</span>
			</a>
		</div>
		<template v-if="is_basic_auth_enabled">
			<div class="inline-flex flex-col gap-2" :class="props.padding ?? 'px-9'">
				<FloatLabel variant="on">
					<InputText id="username" v-model="username" autocomplete="username" :autofocus="true" />
					<label for="username">{{ $t("dialogs.login.username") }}</label>
				</FloatLabel>
			</div>
			<div class="inline-flex flex-col gap-2" :class="props.padding ?? 'px-9'">
				<FloatLabel variant="on">
					<InputPassword id="password" v-model="password" autocomplete="current-password" @keydown.enter="login" />
					<label for="password">{{ $t("dialogs.login.password") }}</label>
				</FloatLabel>
				<Message v-if="invalidPassword" severity="error">{{ $t("dialogs.login.unknown_invalid") }}</Message>
			</div>
			<div class="inline-flex items-center gap-2" :class="props.padding ?? 'px-9'">
				<Checkbox v-model="rememberMe" input-id="remember_me" :binary="true" />
				<label for="remember_me" class="cursor-pointer">{{ $t("dialogs.login.remember_me") }}</label>
			</div>
			<div class="text-muted-color text-right font-semibold" :class="props.padding ?? 'px-9'">
				Lychee <span v-if="is_se_enabled" class="text-primary-500">SE</span>
			</div>
			<div class="flex items-center mt-9">
				<Button
					v-if="closeCallback !== undefined"
					severity="secondary"
					class="w-full font-bold border-none rounded-none ltr:rounded-bl-xl rtl:rounded-br-xl shrink"
					@click="props.closeCallback"
				>
					{{ $t("dialogs.button.cancel") }}
				</Button>
				<Button
					severity="contrast"
					:class="{
						'w-full font-bold border-none shrink': true,
						'rounded-none ltr:rounded-br-xl  rtl:rounded-bl-xl': closeCallback !== undefined,
						'rounded-xl': closeCallback === undefined,
					}"
					@click="login"
				>
					{{ $t("dialogs.login.signin") }}
				</Button>
			</div>
		</template>
		<div v-else class="flex items-center mt-9">
			<Button
				v-if="closeCallback !== undefined"
				severity="secondary"
				:class="{
					'w-full font-bold border-none rounded-none ltr:rounded-bl-xl rtl:rounded-br-xl shrink': true,
					'ltr:rounded-br-xl rtl:rounded-bl-xl': !is_basic_auth_enabled,
				}"
				@click="props.closeCallback"
			>
				{{ $t("dialogs.button.cancel") }}
			</Button>
			<Button
				severity="contrast"
				:class="{
					'w-full font-bold border-none shrink': true,
					'rounded-none ltr:rounded-br-xl rtl:rounded-bl-xl': closeCallback !== undefined,
					'rounded-xl': closeCallback === undefined,
				}"
				@click="login"
			>
				{{ $t("dialogs.login.signin") }}
			</Button>
		</div>
	</form>
</template>
<script setup lang="ts">
import { ref } from "vue";
import FloatLabel from "primevue/floatlabel";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import Message from "primevue/message";
import AuthService from "@/services/auth-service";
import InputText from "@/components/forms/basic/InputText.vue";
import InputPassword from "@/components/forms/basic/InputPassword.vue";
import AlbumService from "@/services/album-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { onMounted } from "vue";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import { useUserStore } from "@/stores/UserState";

const emits = defineEmits<{
	"logged-in": [];
}>();

type OauthProvider = {
	url: string;
	icon: string;
	provider: App.Enum.OauthProvidersType;
};

const props = defineProps<{
	closeCallback?: () => void;
	padding?: string;
}>();

const username = ref("");
const password = ref("");
const rememberMe = ref(false);
const userStore = useUserStore();
const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
const { is_se_enabled, is_basic_auth_enabled, is_webauthn_enabled } = storeToRefs(lycheeStore);
const { is_login_open, is_webauthn_open } = storeToRefs(togglableStore);
const invalidPassword = ref(false);

const oauths = ref<OauthProvider[] | undefined>(undefined);

function login() {
	AuthService.login(username.value, password.value, rememberMe.value)
		.then(() => {
			is_login_open.value = false;
			userStore.setUser(undefined);
			invalidPassword.value = false;
			AlbumService.clearCache();
			emits("logged-in");
		})
		.catch((e) => {
			if (e.response && e.response.status === 401) {
				invalidPassword.value = true;
			}
		});
}

function openWebAuthn() {
	is_login_open.value = false;
	is_webauthn_open.value = true;
	username.value = "";
	password.value = "";
	invalidPassword.value = false;
}

function redirectToOauth() {
	if (is_basic_auth_enabled.value || is_webauthn_enabled.value || oauths.value?.length !== 1) {
		return;
	}

	window.location.href = oauths.value[0].url;
}

onMounted(() => {
	userStore.getOauthData().then((data) => {
		oauths.value = data;
		redirectToOauth();
	});
});
</script>
