<template>
	<div class="h-svh overflow-y-auto">
		<Toolbar class="w-full border-0 h-14 rounded-none">
			<template #start>
				<OpenLeftMenu />
			</template>

			<template #center>
				{{ $t("profile.title") }}
			</template>

			<template #end> </template>
		</Toolbar>
		<SetLogin />
		<SetOauth />
		<SetSecondFactor v-if="is_webauthn_enabled" />
		<SetSharedAlbumsVisibility />

		<!-- Selfie claim button -->
		<div class="flex justify-center mt-6 pb-8">
			<Button :label="$t('people.claim_by_selfie')" icon="pi pi-camera" severity="secondary" outlined @click="isSelfieOpen = true" />
		</div>
		<SelfieClaimModal v-model:visible="isSelfieOpen" />
	</div>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Toolbar from "primevue/toolbar";
import Button from "primevue/button";
import SetLogin from "@/components/forms/profile/SetLogin.vue";
import SetSecondFactor from "@/components/forms/profile/SetSecondFactor.vue";
import SetOauth from "@/components/forms/profile/SetOauth.vue";
import SetSharedAlbumsVisibility from "@/components/forms/profile/SetSharedAlbumsVisibility.vue";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import SelfieClaimModal from "@/components/modals/SelfieClaimModal.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const lycheeStore = useLycheeStateStore();
const { is_webauthn_enabled } = storeToRefs(lycheeStore);

const isSelfieOpen = ref(false);
</script>
