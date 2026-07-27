<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("jobs.title") }}
	</UHeader>
	<UMain class="h-[calc(100vh-var(--ui-header-height))] flex flex-col">
		<UCard class="max-w-7xl mx-auto w-full flex-1 min-h-0 flex flex-col" :ui="{ body: 'flex-1 min-h-0 flex flex-col' }">
			<div v-if="jobs.length === 0" class="text-center">{{ $t("jobs.no_data") }}</div>
			<template v-else>
				<div class="mb-8 shrink-0">
					<div class="flex h-2 rounded-full overflow-hidden w-full bg-elevated">
						<div
							v-for="val in meter"
							:key="val.label"
							:style="{ width: val.value + '%', backgroundColor: val.color }"
							:title="val.label"
						/>
					</div>
					<div class="flex flex-wrap gap-4 mt-2 text-xs text-muted">
						<span v-for="val in meter" :key="`legend-${val.label}`" class="flex items-center gap-1">
							<span class="rounded-full h-2 w-2 inline-block" :style="{ backgroundColor: val.color }" />
							{{ val.label }}
						</span>
					</div>
				</div>
				<UTable
					ref="tableEl"
					:data="jobs"
					:columns="columns"
					sticky
					:virtualize="{ estimateSize: 29, overscan: 12 }"
					class="flex-1 min-h-0"
					:ui="{ base: 'table-fixed', td: 'px-4 py-1' }"
					:meta="{ class: { tr: rowClass } }"
				>
					<template #created_at-cell="{ row }">
						<span class="text-muted">{{ prettyDate(row.original.created_at) }}</span>
					</template>
					<template #status-cell="{ row }">
						<span :class="textCss(row.original.status)">{{ translateStatus(row.original.status) }}</span>
					</template>
					<template #job-cell="{ row }">
						<span class="text-muted">{{ ellispis(row.original.job, 90) }}</span>
					</template>
				</UTable>
			</template>
		</UCard>
	</UMain>
</template>
<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import JobService from "@/services/jobs-service";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import { trans } from "laravel-vue-i18n";
import type { TableColumn, TableRow } from "@nuxt/ui";

type Job = App.Http.Resources.Models.JobHistoryResource;

const jobs = ref<Job[]>([]);
const shouldScroll = ref(true);
const tableEl = ref<{ $el: HTMLElement } | null>(null);

function rowClass(row: TableRow<Job>): string {
	return `job-row-${row.index}`;
}

const columns: TableColumn<Job>[] = [
	{
		accessorKey: "created_at",
		header: trans("jobs.col_date"),
		meta: { class: { th: "w-44", td: "w-44" } },
	},
	{
		accessorKey: "status",
		header: trans("jobs.col_status"),
		meta: { class: { th: "w-28", td: "w-28" } },
	},
	{
		accessorKey: "username",
		header: trans("jobs.col_username"),
		meta: { class: { th: "w-40", td: "w-40 truncate" } },
	},
	{
		accessorKey: "job",
		header: trans("jobs.col_job"),
		meta: { class: { td: "truncate" } },
	},
];
async function load(): Promise<void> {
	return JobService.list().then((response) => {
		jobs.value = response.data.data;
	});
}

function ellispis(str: string, maxLength: number): string {
	if (str.length <= maxLength) {
		return str;
	}
	return str.substring(0, maxLength) + "...";
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
			color: "var(--color-warning-400)",
		});
	}
	if (success_percent > 0) {
		vals.push({
			label: `${translateStatus("success")} — ${success_count}`,
			value: success_percent,
			color: "var(--color-success-700)",
		});
	}
	if (failure_percent > 0) {
		vals.push({
			label: `${translateStatus("failure")} — ${failure_count}`,
			value: failure_percent,
			color: "var(--color-error-700)",
		});
	}
	if (started_percent > 0) {
		vals.push({
			label: `${translateStatus("started")} — ${started_count}`,
			value: started_percent,
			color: "var(--ui-color-primary-500)",
		});
	}
	return vals;
});

function textCss(status: string) {
	switch (status) {
		case "ready":
			return "text-warning-400";
		case "success":
			return "text-success-700";
		case "failure":
			return "text-error-700";
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
				const startedJob = tableEl.value?.$el.querySelector(`.job-row-${idx}`);
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

// The table (and its own internal scroll container) only mounts once jobs load,
// so the listener is attached once tableEl becomes available rather than in onMounted.
watch(tableEl, (el) => {
	el?.$el.addEventListener("scroll", disableAutoScroll);
});

onMounted(() => {
	load();
});
onUnmounted(() => {
	tableEl.value?.$el.removeEventListener("scroll", disableAutoScroll);
	window.clearInterval(intervalId);
});
</script>
