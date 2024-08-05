<template>
	<Toolbar class="w-full border-0">
		<template #start>
			<router-link :to="{ name: 'gallery' }">
				<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="" />
			</router-link>
		</template>

		<template #center>
			{{ $t("lychee.JOBS") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel class="max-w-5xl mx-auto border-none">
		<div v-if="jobs.length === 0" class="text-center">No Jobs have been executed yet.</div>
		<div class="flex" v-for="job in jobs">
			<span class="w-1/4" :class="textCss(job.status)">{{ job.status }}</span>
			<span class="w-1/4">{{ job.username }}</span>
			<span class="w-full text-muted-color">{{ job.job }}</span>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import JobService from "@/services/jobs-service";
import Button from "primevue/button";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import { ref } from "vue";

const jobs = ref([] as App.Http.Resources.Models.JobHistoryResource[]);
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

load();
</script>
