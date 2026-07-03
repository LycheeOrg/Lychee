<template>
	<UCard :ui="{ root: 'max-w-7xl mx-auto' }" dir="ltr">
		<template #header>
			<span class="font-bold">{{ $t("diagnostics.space") }}</span>
		</template>
		<UButton v-if="!requested" icon="prime:refresh" class="w-48 justify-center font-bold" @click="load">{{ $t("diagnostics.load_space") }}</UButton>
		<div v-if="requested && !space" class="text-sky-400 font-bold">{{ $t("diagnostics.loading") }}</div>
		<pre v-if="space"><div v-for="(spaceLine, idx) in space" class=" text-muted font-mono" :key="`space-${idx}`">{{ spaceLine }}</div>
		</pre>
	</UCard>
</template>
<script setup lang="ts">
import { ref } from "vue";
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
