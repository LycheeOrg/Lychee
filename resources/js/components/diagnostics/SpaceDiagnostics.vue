<template>
	<Panel :header="$t('diagnostics.space')" class="border-none max-w-7xl mx-auto">
		<Button icon="pi pi-refresh" class="w-48 border-none font-bold" @click="load" v-if="!requested">{{ $t("diagnostics.load_space") }}</Button>
		<div v-if="requested && !space" class="text-sky-400 font-bold">{{ $t("diagnostics.loading") }}</div>
		<pre v-if="space"><div v-for="space in space" class=" text-muted-color font-mono">{{ space }}</div>
		</pre>
	</Panel>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Button from "primevue/button";
import Panel from "primevue/panel";
import DiagnosticsService from "@/services/diagnostics-service";

const requested = ref(false);
const space = ref<string[] | undefined>(undefined);

function load() {
	requested.value = true;
	DiagnosticsService.space().then((response) => {
		space.value = response.data;
	});
}
</script>
