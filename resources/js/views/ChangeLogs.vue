<template>
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<Button @click="togglableStore.toggleLeftMenu" icon="pi pi-bars" class="mr-2 border-none" severity="secondary" text />
		</template>

		<template #center>
			{{ "Change logs" }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel class="border-none max-w-3xl mx-auto my-12" :pt:header:class="'hidden'" v-for="(changeLog, index) in changeLogs" :key="'cl' + index">
		<h2 class="text-4xl font-bold text-muted-color-emphasis">{{ changeLog.version }}</h2>
		<p class="text-sm text-gray-500">
			{{ changeLog.date }}
		</p>
		<div
			class="mt-4 prose max-w-none dark:prose-invert prose-blockquote:text-muted-color prose-blockquote:m-0 prose-blockquote:text-base prose-code:before:content-[''] prose-code:after:content-[''] prose-li:m-0 prose-a:text-primary-emphasis prose-a:hover:text-primary-emphasis-alt prose-a:no-underline"
			v-html="changeLog.changes"
		></div>
	</Panel>
</template>
<script setup lang="ts">
import InitService from "@/services/init-service";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import Button from "primevue/button";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import { ref } from "vue";

const togglableStore = useTogglablesStateStore();
const changeLogs = ref<App.Http.Resources.Diagnostics.ChangeLogInfo[] | undefined>(undefined);

InitService.fetchChangeLog()
	.then((response) => {
		changeLogs.value = response.data;
	})
	.catch((error) => {
		console.error("Error fetching change logs:", error);
	});
</script>
