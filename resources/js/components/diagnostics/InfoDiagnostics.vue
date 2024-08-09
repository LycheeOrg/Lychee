<template>
	<Panel header="Info" v-if="infos" class="border-none max-w-7xl mx-auto">
		<pre><div v-for="info in infos" class=" text-muted-color font-mono text-sm">{{ info }}</div>
		</pre>
	</Panel>
</template>
<script setup lang="ts">
import DiagnosticsService from "@/services/diagnostics-service";
import Panel from "primevue/panel";
import { ref } from "vue";

const infos = ref(undefined as string[] | undefined);

function load() {
	DiagnosticsService.info().then((response) => {
		infos.value = response.data;
	});
}

load();
</script>
