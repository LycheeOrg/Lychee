<template>
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<router-link :to="{ name: 'gallery' }">
				<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text />
			</router-link>
		</template>

		<template #center>
			{{ $t("lychee.DIAGNOSTICS") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel>
		<ErrorsDiagnotics />
		<InfoDiagnostics v-if="user?.id" />
		<SpaceDiagnostics v-if="user?.id" />
		<ConfigurationsDiagnostics v-if="user?.id" />
	</Panel>
</template>
<script setup lang="ts">
import Toolbar from "primevue/toolbar";
import Button from "primevue/button";
import ConfigurationsDiagnostics from "@/components/diagnostics/ConfigurationsDiagnostics.vue";
import InfoDiagnostics from "@/components/diagnostics/InfoDiagnostics.vue";
import ErrorsDiagnotics from "@/components/diagnostics/ErrorsDiagnostics.vue";
import SpaceDiagnostics from "@/components/diagnostics/SpaceDiagnostics.vue";
import { useAuthStore } from "@/stores/Auth";
import { storeToRefs } from "pinia";

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
authStore.getUser();
</script>
