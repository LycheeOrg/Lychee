<template>
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ $t("jobs.title") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel class="max-w-5xl mx-auto border-none">
		<div v-if="jobs.length === 0" class="text-center">{{ $t("jobs.no_data") }}</div>
		<div class="flex text-xs sm:text-base flex-wrap sm:flex-nowrap" v-for="job in jobs">
			<span class="hidden sm:inline-block sm:w-2/5 text-muted-color">{{ prettyDate(job.created_at) }}</span>
			<span class="w-1/6 sm:w-1/4" :class="textCss(job.status)">{{ job.status }}</span>
			<span class="w-5/6 sm:w-1/4">{{ job.username }}</span>
			<span class="w-1/6 sm:hidden text-muted-color">{{ prettyDate(job.created_at) }}</span>
			<span class="w-5/6 sm:w-full text-2xs sm:text-base text-muted-color-emphasis">{{ job.job }}</span>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import JobService from "@/services/jobs-service";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";

const jobs = ref<App.Http.Resources.Models.JobHistoryResource[]>([]);
function load() {
	JobService.list().then((response) => {
		jobs.value = response.data.data;
	});
}

function textCss(status: string) {
	switch (status) {
		case "ready":
			return "text-ready-400";
		case "success":
			return "text-create-700";
		case "failure":
			return "text-danger-700";
		case "started":
			return "text-primary-500";
		default:
			return "text-primary";
	}
}

function prettyDate(iso8601: string): string {
	return new Date(iso8601).toLocaleString();
}

load();
</script>
