<template>
	<form v-if="oauths !== undefined" class="flex flex-col gap-4 relative max-w-md w-full text-sm rounded-md">
		<div
			:class="{
				'flex justify-center gap-2 w-full': true,
				'flex-col': !is_basic_auth_enabled,
				'flex-row': is_basic_auth_enabled,
			}"
		>
			<a
				v-if="is_webauthn_enabled"
				:class="{
					'inline-block text-xl text-muted transition-all duration-300 hover:text-primary-400 cursor-pointer': true,
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
					'inline-block text-xl text-muted transition-all duration-300 hover:text-primary-400 cursor-pointer': true,
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
			<UFormField :label="$t('dialogs.login.username')">
				<UInput id="username" v-model="username" autocomplete="username" :autofocus="true" class="w-full" />
			</UFormField>
			<UFormField :label="$t('dialogs.login.password')">
				<InputPassword id="password" v-model="password" autocomplete="current-password" @keydown.enter="login" />
				<UAlert v-if="invalidPassword" color="error" variant="soft" class="mt-2" :description="$t('dialogs.login.unknown_invalid')" />
			</UFormField>
			<UCheckbox v-model="rememberMe" :label="$t('dialogs.login.remember_me')" />
			<div class="flex items-center gap-2 mt-4">
				<UButton
					v-if="closeCallback !== undefined"
					color="neutral"
					variant="soft"
					class="flex-1 justify-center font-bold"
					@click="props.closeCallback"
				>
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="primary" class="flex-1 justify-center font-bold" @click="login">
					{{ $t("dialogs.login.signin") }}
				</UButton>
			</div>
		</template>
		<div v-else class="flex items-center gap-2 mt-4">
			<UButton
				v-if="closeCallback !== undefined"
				color="neutral"
				variant="soft"
				class="flex-1 justify-center font-bold"
				@click="props.closeCallback"
			>
				{{ $t("dialogs.button.cancel") }}
			</UButton>
			<UButton color="neutral" class="flex-1 justify-center font-bold" @click="login">
				{{ $t("dialogs.login.signin") }}
			</UButton>
		</div>
	</form>
</template>
<script setup lang="ts">
import { ref } from "vue";
import AuthService from "@/services/auth-service";
import InputPassword from "@/v8/components/forms/basic/InputPassword.vue";
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
const { is_basic_auth_enabled, is_webauthn_enabled } = storeToRefs(lycheeStore);
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
