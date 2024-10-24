<template>
	<Fieldset
		:legend="creadentialsTitle"
		:toggleable="true"
		class="border-b-0 border-r-0 rounded-r-none rounded-b-none mb-4 hover:border-primary-500 pt-2 max-w-xl mx-auto"
		:pt:legendlabel:class="'capitalize'"
		v-if="u2f"
	>
		<div class="text-muted-color text-center">This only provides the ability to use WebAuthn to authenticate instead of username & password.</div>
		<SetSecondFactorLine v-for="credential in u2f" :key="credential.id" :u2f="credential" @delete="deleteU2F" />
		<div v-if="u2f.length === 0">
			<p class="text-muted-color text-center">Credentials list is empty!</p>
		</div>

		<div class="w-full text-lg font-bold" v-if="isWebAuthnUnavailable">
			<h1 class="p-3 text-center w-full">{{ $t("lychee.U2F_NOT_SECURE") }}</h1>
		</div>
		<div class="w-full mt-4" v-if="!isWebAuthnUnavailable">
			<Button class="border-0 bg-surface text-create-600 font-bold hover:bg-create-600 hover:text-white w-full" @click="register">
				{{ $t("lychee.U2F_REGISTER_KEY") }}
			</Button>
		</div>
	</Fieldset>
</template>
<script setup lang="ts">
import WebAuthnService from "@/services/webauthn-service";
import { computed, ref } from "vue";
import SetSecondFactorLine from "@/components/forms/profile/SetSecondFactorLine.vue";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import Button from "primevue/button";
import Fieldset from "primevue/fieldset";

const u2f = ref(undefined as App.Http.Resources.Models.WebAuthnResource[] | undefined);
const toast = useToast();

const isWebAuthnUnavailable = computed(() => WebAuthnService.isWebAuthnUnavailable());

const creadentialsTitle = computed(() => trans("Passkey/MFA/2FA"));

function refresh() {
	WebAuthnService.get().then((response) => {
		u2f.value = response.data;
	});
}

function deleteU2F(id: string) {
	WebAuthnService.delete(id).then(() => {
		toast.add({ severity: "success", summary: "Change saved!", detail: trans("lychee.U2F_CREDENTIALS_DELETED"), life: 3000 });
		refresh();
	});
}

function register() {
	WebAuthnService.register()
		.then(() => {
			toast.add({ severity: "success", summary: "Change saved!", detail: trans("lychee.U2F_REGISTRATION_SUCCESS"), life: 3000 });
			refresh();
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("lychee.ERROR_TEXT"), detail: e.message, life: 3000 });
		});
}

refresh();
</script>
