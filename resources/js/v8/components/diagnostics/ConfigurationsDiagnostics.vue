<template>
	<UCard v-if="configs" :ui="{ root: 'max-w-7xl mx-auto' }" dir="ltr">
		<template #header>
			<span class="font-bold">{{ $t("diagnostics.configuration") }}</span>
		</template>
		<pre><div v-for="(config, idx) in configs" class=" text-muted font-mono text-sm" :key="`config-${idx}`">{{ config }}</div>
		</pre>
	</UCard>
</template>
<script setup lang="ts">
import { ref } from "vue";
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
