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
	<Panel class="max-w-7xl mx-auto border-none">
		<div v-if="jobs.length === 0" class="text-center">{{ $t("jobs.no_data") }}</div>
		<template v-else>
			<div class="mb-8">
				<MeterGroup :value="meter" />
			</div>
			<div class="flex text-xs sm:text-base flex-wrap sm:flex-nowrap" v-for="job in jobs">
				<span class="hidden sm:inline-block sm:w-2/5 text-muted-color">{{ prettyDate(job.created_at) }}</span>
				<span class="w-1/6 sm:w-1/4" :class="textCss(job.status)">{{ translateStatus(job.status) }}</span>
				<span class="w-5/6 sm:w-1/4">{{ job.username }}</span>
				<span class="w-1/6 sm:hidden text-muted-color">{{ prettyDate(job.created_at) }}</span>
				<span class="w-5/6 sm:w-full text-2xs sm:text-base text-muted-color">{{ job.job }}</span>
			</div>
		</template>
	</Panel>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import JobService from "@/services/jobs-service";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import MeterGroup from "primevue/metergroup";
import { computed } from "vue";
import { onMounted } from "vue";
import { trans } from "laravel-vue-i18n";

const jobs = ref<App.Http.Resources.Models.JobHistoryResource[]>([]);
function load() {
	JobService.list().then((response) => {
		jobs.value = response.data.data;
	});
}

const meter = computed(() => {
	const vals = [];

	if (jobs.value.length === 0) {
		return [];
	}

	const ready_count = (jobs.value.filter((j) => j.status === "ready").length * 100) / jobs.value.length;
	const success_count = (jobs.value.filter((j) => j.status === "success").length * 100) / jobs.value.length;
	const failure_count = (jobs.value.filter((j) => j.status === "failure").length * 100) / jobs.value.length;
	const started_count = (jobs.value.filter((j) => j.status === "started").length * 100) / jobs.value.length;

	if (ready_count > 0) {
		vals.push({
			label: translateStatus("ready"),
			value: ready_count,
			color: "var(--color-ready-400)",
		});
	}
	if (success_count > 0) {
		vals.push({
			label: translateStatus("success"),
			value: success_count,
			color: "var(--color-create-700)",
		});
	}
	if (failure_count > 0) {
		vals.push({
			label: translateStatus("failure"),
			value: failure_count,
			color: "var(--color-danger-700)",
		});
	}
	if (started_count > 0) {
		vals.push({
			label: translateStatus("started"),
			value: started_count,
			color: "var(--color-primary-500)",
		});
	}
	return vals;
});

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

function translateStatus(status: string): string {
	switch (status) {
		case "ready":
			return trans("jobs.ready");
		case "success":
			return trans("jobs.success");
		case "failure":
			return trans("jobs.failure");
		case "started":
			return trans("jobs.started");
		default:
			return status;
	}
}

onMounted(() => {
	load();
});
</script>
