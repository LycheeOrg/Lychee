<template>
	<Panel v-if="infos" :header="$t('diagnostics.info')" class="border-none max-w-7xl mx-auto" dir="ltr">
		<pre><div v-for="(info, idx) in infos" class=" text-muted-color font-mono text-sm" :key="`info-${idx}`">{{ info }}</div>
		</pre>
	</Panel>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Panel from "primevue/panel";
import DiagnosticsService from "@/services/diagnostics-service";

const infos = ref<string[] | undefined>(undefined);
const emits = defineEmits<{
	loaded: [data: string[]];
}>();

function load() {
	DiagnosticsService.info().then((response) => {
		infos.value = response.data;
		emits("loaded", response.data);
	});
}

load();
</script>
