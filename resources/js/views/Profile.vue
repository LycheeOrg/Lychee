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
	</div>
</template>
<script setup lang="ts">
import Toolbar from "primevue/toolbar";
import SetLogin from "@/components/forms/profile/SetLogin.vue";
import SetSecondFactor from "@/components/forms/profile/SetSecondFactor.vue";
import SetOauth from "@/components/forms/profile/SetOauth.vue";
import SetSharedAlbumsVisibility from "@/components/forms/profile/SetSharedAlbumsVisibility.vue";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const lycheeStore = useLycheeStateStore();
const { is_webauthn_enabled } = storeToRefs(lycheeStore);
</script>
