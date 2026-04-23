<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>
		<template #center>
			{{ $t("admin-dashboard.title") }}
		</template>
		<template #end>
			<Button
				v-if="initData?.settings.can_edit"
				:label="$t('admin-dashboard.refresh')"
				icon="pi pi-refresh"
				:disabled="isLoading"
				severity="secondary"
				class="border-none"
				@click="refreshStats"
			/>
		</template>
	</Toolbar>

	<div class="admin-dashboard max-w-7xl mx-auto p-4">
		<!-- Security Advisories (only for full admins, shown when vulnerabilities are found) -->
		<Panel v-if="initData?.settings.can_edit && advisories.length > 0" class="mb-8 border-none">
			<template #header>
				<div class="flex items-center gap-2 text-orange-400 font-bold">
					<i class="pi pi-exclamation-triangle text-lg" />
					<span>{{ $t("admin-dashboard.security.title") }}</span>
				</div>
			</template>
			<p class="mb-4 text-muted-color text-sm">{{ $t("admin-dashboard.security.description") }}</p>
			<ul class="space-y-3">
				<li v-for="advisory in advisories" :key="advisory.ghsa_id" class="flex flex-col gap-1">
					<div class="flex items-center gap-2 font-semibold">
						<span class="text-orange-400">•</span>
						<a
							:href="`https://github.com/LycheeOrg/Lychee/security/advisories/${advisory.ghsa_id}`"
							target="_blank"
							rel="noopener noreferrer"
							class="text-primary-400 hover:text-primary-300 underline"
						>
							{{ advisory.cve_id ?? advisory.ghsa_id }}
						</a>
						<span class="text-muted-color text-xs">
							{{ advisory.cvss_score !== null ? `CVSS ${advisory.cvss_score.toFixed(1)}` : $t("admin-dashboard.security.no_cvss") }}
						</span>
					</div>
					<p class="ltr:ml-4 rtl:mr-4 text-muted-color text-xs">{{ advisory.summary }}</p>
				</li>
			</ul>
		</Panel>

		<!-- Stats Overview (only for full admins with settings.can_edit) -->
		<Panel v-if="initData?.settings.can_edit" class="mb-8 border-none">
			<template #header>
				<h2 class="text-xl font-semibold">{{ $t("admin-dashboard.overview") }}</h2>
			</template>
			<div v-if="stats" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
				<div class="bg-surface-100 dark:bg-surface-800 rounded p-4 text-center">
					<div class="text-2xl font-bold">{{ stats.photos_count.toLocaleString() }}</div>
					<div class="text-muted-color text-sm">{{ $t("admin-dashboard.metrics.photos_count") }}</div>
				</div>
				<div class="bg-surface-100 dark:bg-surface-800 rounded p-4 text-center">
					<div class="text-2xl font-bold">{{ stats.albums_count.toLocaleString() }}</div>
					<div class="text-muted-color text-sm">{{ $t("admin-dashboard.metrics.albums_count") }}</div>
				</div>
				<div class="bg-surface-100 dark:bg-surface-800 rounded p-4 text-center">
					<div class="text-2xl font-bold">{{ stats.users_count.toLocaleString() }}</div>
					<div class="text-muted-color text-sm">{{ $t("admin-dashboard.metrics.users_count") }}</div>
				</div>
				<div class="bg-surface-100 dark:bg-surface-800 rounded p-4 text-center">
					<div class="text-2xl font-bold">{{ formatBytes(stats.storage_bytes) }}</div>
					<div class="text-muted-color text-sm">{{ $t("admin-dashboard.metrics.storage_bytes") }}</div>
				</div>
				<div class="bg-surface-100 dark:bg-surface-800 rounded p-4 text-center">
					<div class="text-2xl font-bold">{{ stats.queued_jobs }}</div>
					<div class="text-muted-color text-sm">{{ $t("admin-dashboard.metrics.queued_jobs") }}</div>
				</div>
				<div class="bg-surface-100 dark:bg-surface-800 rounded p-4 text-center">
					<div class="text-2xl font-bold">{{ stats.failed_jobs_24h }}</div>
					<div class="text-muted-color text-sm">{{ $t("admin-dashboard.metrics.failed_jobs_24h") }}</div>
				</div>
				<div class="bg-surface-100 dark:bg-surface-800 rounded p-4 text-center col-span-2">
					<div class="text-lg font-bold">{{ stats.last_successful_job_at ?? "—" }}</div>
					<div class="text-muted-color text-sm">{{ $t("admin-dashboard.metrics.last_successful_job_at") }}</div>
				</div>
			</div>
			<div v-if="stats && stats.errors.length > 0" class="mt-2 text-orange-500 text-sm">
				{{ $t("admin-dashboard.errors.partial") }}
			</div>
			<div v-if="!stats && !isLoading" class="text-muted-color text-center py-4">
				<ProgressSpinner style="width: 2rem; height: 2rem" />
			</div>
		</Panel>

		<!-- Tools grid -->
		<Panel class="border-none">
			<template #header>
				<h2 class="text-xl font-semibold">{{ $t("admin-dashboard.tools") }}</h2>
			</template>
			<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
				<template v-for="tile in tiles" :key="tile.key">
					<component
						:is="tile.isExternal ? 'a' : RouterLink"
						v-if="tile.visible.value"
						:to="tile.isExternal ? undefined : tile.to"
						:href="tile.isExternal ? tile.to : undefined"
						:target="tile.isExternal ? '_blank' : undefined"
						class="bg-surface-100 dark:bg-surface-800 hover:bg-surface-200 dark:hover:bg-surface-700 rounded p-4 text-center flex flex-col items-center gap-2 cursor-pointer no-underline text-color"
						tabindex="0"
						@keydown.enter="navigateTile(tile)"
						@keydown.space.prevent="navigateTile(tile)"
					>
						<OverlayBadge v-if="tile.num && tile.num.value > 0" severity="primary" :pt:badge:class="' outline-0'">
							<PiMiniIcon :icon="tile.icon" class="w-6 h-6 text-2xl fill-surface-0" />
						</OverlayBadge>
						<PiMiniIcon v-else :icon="tile.icon" class="w-6 h-6 text-2xl fill-surface-0" />
						<span class="text-sm">{{ $t(tile.label) }}</span>
					</component>
				</template>
			</div>
		</Panel>
	</div>
</template>

<script lang="ts" setup>
import { ref, onMounted } from "vue";
import OverlayBadge from "primevue/overlaybadge";
import { RouterLink, useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { useToast } from "primevue/usetoast";
import Toolbar from "primevue/toolbar";
import Button from "primevue/button";
import Panel from "primevue/panel";
import { trans } from "laravel-vue-i18n";
import ProgressSpinner from "primevue/progressspinner";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import PiMiniIcon from "@/components/icons/PiMiniIcon.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import SecurityAdvisoriesService from "@/services/security-advisories-service";
import AdminStatsService from "@/services/admin-stats-service";
import { useAdminTiles, type AdminTile } from "@/composables/useAdminTiles";

const lycheeStore = useLycheeStateStore();
const leftMenuStore = useLeftMenuStateStore();
const toast = useToast();
const router = useRouter();

const { initData } = storeToRefs(leftMenuStore);
const stats = ref<App.Http.Resources.Models.AdminStatsResource | null>(null);
const isLoading = ref(false);
const advisories = ref<App.Http.Resources.Models.SecurityAdvisoryResource[]>([]);

const tiles: AdminTile[] = useAdminTiles(lycheeStore, leftMenuStore);

function loadStats() {
	isLoading.value = true;
	AdminStatsService.getStats(false)
		.then((response) => {
			stats.value = response.data;
			isLoading.value = false;
			if (response.data.errors.length > 0) {
				toast.add({ severity: "warn", summary: trans("toasts.error"), detail: response.data.errors.join("; "), life: 5000 });
			}
		})
		.catch(() => {
			isLoading.value = false;
		});
}

function refreshStats() {
	isLoading.value = true;
	AdminStatsService.getStats(true)
		.then((response) => {
			stats.value = response.data;
			isLoading.value = false;
			if (response.data.errors.length > 0) {
				toast.add({ severity: "warn", summary: trans("toasts.error"), detail: response.data.errors.join("; "), life: 5000 });
			}
		})
		.catch(() => {
			isLoading.value = false;
		});
}

function formatBytes(bytes: number): string {
	if (bytes === 0) return "0 B";
	const k = 1024;
	const sizes = ["B", "KB", "MB", "GB", "TB"];
	const i = Math.floor(Math.log(bytes) / Math.log(k));
	return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
}

function navigateTile(tile: AdminTile) {
	if (tile.isExternal) {
		window.open(tile.to, "_blank");
	} else {
		router.push(tile.to);
	}
}

function loadAdvisories() {
	SecurityAdvisoriesService.getAdvisories()
		.then((response) => {
			advisories.value = response.data;
		})
		.catch(() => {
			// Network errors: silently ignore.
		});
}

onMounted(() => {
	if (initData.value?.settings.can_edit) {
		loadStats();
		loadAdvisories();
	}
});
</script>
