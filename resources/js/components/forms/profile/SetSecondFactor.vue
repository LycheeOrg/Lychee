<template>
	<Fieldset
		:legend="$t('profile.u2f.header')"
		:toggleable="true"
		class="border-b-0 border-r-0 rounded-r-none rounded-b-none mb-4 hover:border-primary-500 pt-2 max-w-xl mx-auto"
		v-if="u2f"
	>
		<div class="text-muted-color text-center">{{ $t("profile.u2f.info") }}</div>
		<SetSecondFactorLine v-for="credential in u2f" :key="credential.id" :u2f="credential" @delete="deleteU2F" />
		<div v-if="u2f.length === 0">
			<p class="text-muted-color text-center">{{ $t("profile.u2f.empty") }}</p>
		</div>

		<div class="w-full text-lg font-bold" v-if="isWebAuthnUnavailable">
			<h1 class="p-3 text-center w-full">{{ $t("profile.u2f.not_secure") }}</h1>
		</div>
		<div class="w-full mt-4" v-if="!isWebAuthnUnavailable">
			<Button class="border-0 bg-transparent text-create-600 font-bold hover:bg-create-600 hover:text-white w-full" @click="register">
				{{ $t("profile.u2f.new") }}
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

const u2f = ref<App.Http.Resources.Models.WebAuthnResource[] | undefined>(undefined);
const toast = useToast();

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
