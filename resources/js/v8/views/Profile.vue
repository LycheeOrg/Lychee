<template>
	<div class="h-svh overflow-y-auto">
		<UHeader :toggle="false">
			<template #left>
				<OpenLeftMenu />
			</template>
			{{ $t("profile.title") }}
		</UHeader>
		<UContainer class="max-w-3xl mx-auto flex flex-col gap-4">
			<SetLogin />
			<SetOauth />
			<SetSecondFactor v-if="is_webauthn_enabled" />
			<SetSharedAlbumsVisibility />
		</UContainer>

		<!-- Selfie claim button -->
		<div class="flex justify-center mt-6 pb-8">
			<UButton
				:label="$t('people.claim_by_selfie')"
				icon="lucide:camera"
				color="neutral"
				variant="outline"
				@click="
					() => {
						isSelfieOpen = true;
					}
				"
			/>
		</div>
		<SelfieClaimModal v-model:visible="isSelfieOpen" />
	</div>
</template>
<script setup lang="ts">
import { ref } from "vue";
import SetLogin from "@/v8/components/forms/profile/SetLogin.vue";
import SetSecondFactor from "@/v8/components/forms/profile/SetSecondFactor.vue";
import SetOauth from "@/v8/components/forms/profile/SetOauth.vue";
import SetSharedAlbumsVisibility from "@/v8/components/forms/profile/SetSharedAlbumsVisibility.vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import SelfieClaimModal from "@/v8/components/modals/faceRecog/SelfieClaimModal.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const lycheeStore = useLycheeStateStore();
const { is_webauthn_enabled } = storeToRefs(lycheeStore);

const isSelfieOpen = ref(false);
</script>
