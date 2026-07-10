<template>
	<Fieldset v-if="u2f" :legend="$t('profile.u2f.header')" :toggleable="true" class="hover:border-primary pt-2">
		<div class="text-muted text-center">{{ $t("profile.u2f.info") }}</div>
		<SetSecondFactorLine v-for="credential in u2f" :key="credential.id" :u2f="credential" @delete="deleteU2F" />
		<div v-if="u2f.length === 0">
			<p class="text-muted text-center">{{ $t("profile.u2f.empty") }}</p>
		</div>

		<div v-if="isWebAuthnUnavailable" class="w-full text-lg font-bold">
			<h1 class="p-3 text-center w-full">{{ $t("profile.u2f.not_secure") }}</h1>
		</div>
		<div v-if="!isWebAuthnUnavailable" class="w-full mt-4">
			<UButton color="success" variant="ghost" class="w-full font-bold justify-center" @click="register">
				{{ $t("profile.u2f.new") }}
			</UButton>
		</div>
	</Fieldset>
</template>
<script setup lang="ts">
import WebAuthnService from "@/services/webauthn-service";
import { computed, ref } from "vue";
import SetSecondFactorLine from "@/v8/components/forms/profile/SetSecondFactorLine.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";

const u2f = ref<App.Http.Resources.Models.WebAuthnResource[] | undefined>(undefined);
const toast = useAppToast();

const isWebAuthnUnavailable = computed(() => WebAuthnService.isWebAuthnUnavailable());

function refresh() {
	WebAuthnService.get().then((response) => {
		u2f.value = response.data;
	});
}

function deleteU2F(id: string) {
	WebAuthnService.delete(id).then(() => {
		toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("profile.u2f.credential_deleted"), life: 3000 });
		refresh();
	});
}

function register() {
	WebAuthnService.register()
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("profile.u2f.credential_registred"), life: 3000 });
			refresh();
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.message, life: 3000 });
		});
}

refresh();
</script>
