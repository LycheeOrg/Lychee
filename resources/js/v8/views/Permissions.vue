<template>
	<UHeader :toggle="false">
		<template #left>
			<router-link :to="{ name: 'gallery' }">
				<UButton icon="prime:angle-left" color="neutral" variant="ghost" />
			</router-link>
		</template>
		{{ $t("diagnostics.title") }}
	</UHeader>
	<UCard v-if="left.length && right.length" class="p-9">
		<div class="grid" style="grid-template-columns: 1fr 1fr 1fr">
			<div></div>
			<div>
				<template v-for="i in Math.min(left.length, right.length)" :key="`line-${i}`">
					<pre v-if="left[i] !== right[i]" class="text-success">{{ i }} - {{ left[i] }}</pre>
					<pre v-if="left[i] !== right[i]" class="text-error">{{ i }} + {{ right[i] }}</pre>
				</template>
				<pre v-if="same" class="font-bold text-success text-center">{{ $t("diagnostics.identical_content") }}</pre>
			</div>
			<div></div>
		</div>
		<div class="grid text-muted" style="grid-template-columns: 1fr 1fr 1fr 1fr">
			<div></div>
			<pre>{{ left.join("\n") }}</pre>
			<pre>{{ right.join("\n") }}</pre>
			<div></div>
		</div>
	</UCard>
</template>
<script setup lang="ts">
import { ref } from "vue";
import DiagnosticsService from "@/services/diagnostics-service";

const same = ref(true);
const left = ref<string[]>([]);
const right = ref<string[]>([]);

DiagnosticsService.permissions().then((response) => {
	left.value = response.data.left.split("\n");
	right.value = response.data.right.split("\n");
	same.value = response.data.left === response.data.right;
});
</script>
