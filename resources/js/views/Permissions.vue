<template>
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<router-link :to="{ name: 'gallery' }">
				<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text />
			</router-link>
		</template>

		<template #center>
			{{ $t("diagnostics.title") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel v-if="left.length && right.length" class="border-none p-9">
		<div class="grid" style="grid-template-columns: 1fr 1fr 1fr">
			<div></div>
			<div>
				<template v-for="i in Math.min(left.length, right.length)">
					<pre v-if="left[i] !== right[i]" class="text-create-600">{{ i }} - {{ left[i] }}</pre>
					<pre v-if="left[i] !== right[i]" class="text-danger-600">{{ i }} + {{ right[i] }}</pre>
				</template>
				<pre v-if="same" class="font-bold text-create-700 text-center">{{ $t("diagnostics.identical_content") }}</pre>
			</div>
			<div></div>
		</div>
		<div class="grid text-muted-color" style="grid-template-columns: 1fr 1fr 1fr 1fr">
			<div></div>
			<pre>{{ left.join("\n") }}</pre>
			<pre>{{ right.join("\n") }}</pre>
			<div></div>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Toolbar from "primevue/toolbar";
import Button from "primevue/button";
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
