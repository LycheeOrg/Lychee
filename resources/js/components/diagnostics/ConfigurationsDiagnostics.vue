<template>
	<Panel v-if="configs" :header="$t('diagnostics.configuration')" class="border-none max-w-7xl mx-auto" dir="ltr">
		<pre><div v-for="(config, idx) in configs" class=" text-muted-color font-mono text-sm" :key="`config-${idx}`">{{ config }}</div>
		</pre>
	</Panel>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Panel from "primevue/panel";
import DiagnosticsService from "@/services/diagnostics-service";

const configs = ref<string[] | undefined>(undefined);

const emits = defineEmits<{
	loaded: [data: string[]];
}>();

function load() {
	DiagnosticsService.config().then((response) => {
		configs.value = response.data;
		emits("loaded", response.data);
	});
}

load();
</script>
