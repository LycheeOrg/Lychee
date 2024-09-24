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
		<InfoDiagnostics v-if="user" />
		<SpaceDiagnostics v-if="user" />
		<ConfigurationsDiagnostics v-if="user" />
	</Panel>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Toolbar from "primevue/toolbar";
import Button from "primevue/button";
import ConfigurationsDiagnostics from "@/components/diagnostics/ConfigurationsDiagnostics.vue";
import InfoDiagnostics from "@/components/diagnostics/InfoDiagnostics.vue";
import ErrorsDiagnotics from "@/components/diagnostics/ErrorsDiagnostics.vue";
import SpaceDiagnostics from "@/components/diagnostics/SpaceDiagnostics.vue";
import AuthService from "@/services/auth-service";

const user = ref(undefined as App.Http.Resources.Models.UserResource | undefined);

AuthService.user().then((response) => {
	user.value = response.data;
});
</script>
