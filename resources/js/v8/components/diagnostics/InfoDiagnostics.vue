<template>
	<UCard v-if="infos" :ui="{ root: 'max-w-7xl mx-auto' }" dir="ltr">
		<template #header>
			<span class="font-bold">{{ $t("diagnostics.info") }}</span>
		</template>
		<pre><div v-for="(info, idx) in infos" class=" text-muted font-mono text-sm" :key="`info-${idx}`">{{ info }}</div>
		</pre>
	</UCard>
</template>
<script setup lang="ts">
import { ref } from "vue";
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
