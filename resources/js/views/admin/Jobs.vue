<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
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
			<div
				v-for="(job, idx) in jobs"
				class="flex text-xs sm:text-base flex-wrap sm:flex-nowrap"
				dir="ltr"
				:key="`job-${idx}`"
				:id="`job${idx}`"
			>
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
import { onUnmounted, ref } from "vue";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import JobService from "@/services/jobs-service";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import MeterGroup from "primevue/metergroup";
import { computed } from "vue";
import { onMounted } from "vue";
import { trans } from "laravel-vue-i18n";

const jobs = ref<App.Http.Resources.Models.JobHistoryResource[]>([]);
const shouldScroll = ref(true);
async function load(): Promise<void> {
	return JobService.list().then((response) => {
		jobs.value = response.data.data;
	});
}

const meter = computed(() => {
	const vals = [];

	if (jobs.value.length === 0) {
		return [];
	}

	const ready_count = jobs.value.filter((j) => j.status === "ready").length;
	const success_count = jobs.value.filter((j) => j.status === "success").length;
	const failure_count = jobs.value.filter((j) => j.status === "failure").length;
	const started_count = jobs.value.filter((j) => j.status === "started").length;

	const ready_percent = (ready_count * 100) / jobs.value.length;
	const success_percent = (success_count * 100) / jobs.value.length;
	const failure_percent = (failure_count * 100) / jobs.value.length;
	const started_percent = (started_count * 100) / jobs.value.length;

	if (ready_percent > 0) {
		vals.push({
			label: `${translateStatus("ready")} — ${ready_count}`,
			value: ready_percent,
			color: "var(--color-ready-400)",
		});
	}
	if (success_percent > 0) {
		vals.push({
			label: `${translateStatus("success")} — ${success_count}`,
			value: success_percent,
			color: "var(--color-create-700)",
		});
	}
	if (failure_percent > 0) {
		vals.push({
			label: `${translateStatus("failure")} — ${failure_count}`,
			value: failure_percent,
			color: "var(--color-danger-700)",
		});
	}
	if (started_percent > 0) {
		vals.push({
			label: `${translateStatus("started")} — ${started_count}`,
			value: started_percent,
			color: "var(--p-primary-500)",
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
	const translationMap = new Map([
		["ready", trans("jobs.ready")],
		["success", trans("jobs.success")],
		["failure", trans("jobs.failure")],
		["started", trans("jobs.started")],
	]);

	return translationMap.get(status) || status;
}

const intervalId = setInterval(() => {
	const ready_count = jobs.value.filter((j) => j.status === "ready").length;
	const started_count = jobs.value.filter((j) => j.status === "started").length;
	if (ready_count > 0 || started_count > 0) {
		console.log("Reloading jobs...");
		load().then(() => {
			// Auto-scroll to the latest started job.
			if (!shouldScroll.value) {
				return;
			}

			const idx = jobs.value.findLastIndex((j) => j.status === "started");
			if (idx !== -1) {
				const startedJob = document.getElementById(`job${idx}`);
				startedJob?.scrollIntoView({ behavior: "smooth", block: "center" });
			}
		});

		return;
	}

	window.clearInterval(intervalId);
}, 2000);

function disableAutoScroll() {
	shouldScroll.value = false;

	// Re-enable auto-scroll after 10 seconds of inactivity
	setTimeout(() => {
		shouldScroll.value = true;
	}, 10000);
}

onMounted(() => {
	load();
	addEventListener("scroll", disableAutoScroll);
});
onUnmounted(() => {
	removeEventListener("scroll", disableAutoScroll);
	window.clearInterval(intervalId);
});
</script>
