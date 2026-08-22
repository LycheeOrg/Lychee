<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>

		{{ $t("admin-dashboard.title") }}

		<template #right>
			<UButton
				v-if="initData?.settings.can_edit"
				:label="$t('admin-dashboard.refresh')"
				icon="lucide:refresh-cw"
				:disabled="isLoading"
				color="neutral"
				variant="ghost"
				@click="refreshStats"
			/>
		</template>
	</UHeader>

	<div class="admin-dashboard max-w-7xl mx-auto p-4">
		<!-- Update Status (only for full admins) -->
		<UCard v-if="initData?.settings.can_edit && updateStatus?.enabled && updateStatus?.has_update" class="mb-4">
			<template #header>
				<div class="flex items-center gap-2 font-bold text-primary-500">
					<UIcon name="lucide:circle-arrow-up" class="text-lg" />
					<span>{{ $t("admin-dashboard.update.title") }}</span>
				</div>
			</template>
			<p class="text-sm text-muted">
				{{
					$t("admin-dashboard.update.update_available", {
						current: updateStatus.current_version ?? "?",
						latest: updateStatus.latest_version ?? "?",
					})
				}}
			</p>
		</UCard>

		<!-- Security Advisories (only for full admins, shown when vulnerabilities are found) -->
		<UCard v-if="initData?.settings.can_edit && advisories.length > 0" class="mb-4">
			<template #header>
				<div class="flex items-center gap-2 text-orange-400 font-bold">
					<UIcon name="lucide:triangle-alert" class="text-lg" />
					<span>{{ $t("admin-dashboard.security.title") }}</span>
				</div>
			</template>
			<p class="mb-4 text-muted text-sm">{{ $t("admin-dashboard.security.description") }}</p>
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
						<span class="text-muted text-xs">
							{{ advisory.cvss_score !== null ? `CVSS ${advisory.cvss_score.toFixed(1)}` : $t("admin-dashboard.security.no_cvss") }}
						</span>
					</div>
					<p class="ltr:ml-4 rtl:mr-4 text-muted text-xs">{{ advisory.summary }}</p>
				</li>
			</ul>
		</UCard>

		<!-- Stats Overview (only for full admins with settings.can_edit) -->
		<UCard v-if="initData?.settings.can_edit" class="mb-4">
			<template #header>
				<h2 class="text-xl font-semibold">{{ $t("admin-dashboard.overview") }}</h2>
			</template>
			<div v-if="stats" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
				<AdminStatTile label="admin-dashboard.metrics.photos_count" :value="stats.photos_count.toLocaleString()" />
				<AdminStatTile label="admin-dashboard.metrics.albums_count" :value="stats.albums_count.toLocaleString()" />
				<AdminStatTile label="admin-dashboard.metrics.users_count" :value="stats.users_count.toLocaleString()" />
				<AdminStatTile label="admin-dashboard.metrics.storage_bytes" :value="formatBytes(stats.storage_bytes)" ltr />
				<AdminStatTile label="admin-dashboard.metrics.queued_jobs" :value="stats.queued_jobs" />
				<AdminStatTile label="admin-dashboard.metrics.failed_jobs_24h" :value="stats.failed_jobs_24h" />
				<AdminStatTile label="admin-dashboard.metrics.last_successful_job_at" :value="stats.last_successful_job_at ?? '—'" small wide />
			</div>
			<div v-if="stats && stats.errors.length > 0" class="mt-2 text-orange-500 text-sm">
				{{ $t("admin-dashboard.errors.partial") }}
			</div>
			<div v-if="!stats && !isLoading" class="text-muted text-center py-4">
				<LycheeLoadingIcon fast class="text-2xl" />
			</div>
		</UCard>

		<!-- Tools grid -->
		<UCard>
			<template #header>
				<h2 class="text-xl font-semibold">{{ $t("admin-dashboard.tools") }}</h2>
			</template>
			<div class="flex flex-col gap-6">
				<div v-for="section in tileSections" :key="section.key" v-show="section.tiles.length > 0">
					<h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-muted">{{ $t(section.label) }}</h3>
					<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
						<AdminTileLink v-for="tile in section.tiles" :key="tile.key" :tile="tile" />
					</div>
				</div>
			</div>
		</UCard>

		<div class="flex items-center justify-center gap-6 mt-6 text-sm">
			<a
				v-if="initData?.settings.can_edit && !lycheeStore.is_white_label_enabled"
				:href="`${Constants.BASE_URL}/docs/api`"
				target="_blank"
				rel="noopener noreferrer"
				class="text-muted hover:text-default underline"
			>
				{{ $t("left-menu.api") }}
			</a>
			<RouterLink :to="{ name: 'changelogs' }" class="text-muted hover:text-default underline">
				{{ $t("left-menu.changelog") }}
			</RouterLink>
		</div>
	</div>
</template>

<script lang="ts" setup>
import { ref, onMounted, computed } from "vue";
import { RouterLink } from "vue-router";
import { storeToRefs } from "pinia";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import AdminTileLink from "@/v8/components/admin/AdminTileLink.vue";
import AdminStatTile from "@/v8/components/admin/AdminStatTile.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import Constants from "@/services/constants";
import SecurityAdvisoriesService from "@/services/security-advisories-service";
import AdminStatsService, { type AdminUpdateStatusResource } from "@/services/admin-stats-service";
import { useAdminTiles, type AdminTile, type AdminTileGroup } from "@/v8/composables/useAdminTiles";

const lycheeStore = useLycheeStateStore();
const leftMenuStore = useLeftMenuStateStore();
const toast = useAppToast();

const { initData } = storeToRefs(leftMenuStore);
const stats = ref<App.Http.Resources.Models.AdminStatsResource | null>(null);
const isLoading = ref(false);
const advisories = ref<App.Http.Resources.Models.SecurityAdvisoryResource[]>([]);
const updateStatus = ref<AdminUpdateStatusResource | null>(null);

const tiles: AdminTile[] = useAdminTiles(lycheeStore, leftMenuStore);

const tileGroupLabelMap: Record<AdminTileGroup, string> = {
	core: "admin-dashboard.tool_groups.core",
	monitoring: "admin-dashboard.tool_groups.monitoring",
	extensions: "admin-dashboard.tool_groups.extensions",
};

const tileSections = computed(() => {
	const orderedGroups: AdminTileGroup[] = ["core", "monitoring", "extensions"];

	return orderedGroups.map((group) => ({
		key: group,
		label: tileGroupLabelMap[group],
		tiles: tiles.filter((tile) => tile.group === group && tile.visible.value),
	}));
});

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

function loadAdvisories() {
	SecurityAdvisoriesService.getAdvisories()
		.then((response) => {
			advisories.value = response.data;
		})
		.catch(() => {
			// Network errors: silently ignore.
		});
}

function loadUpdateStatus() {
	AdminStatsService.getUpdateStatus()
		.then((response) => {
			updateStatus.value = response.data;
		})
		.catch(() => {
			// Network errors: silently ignore.
		});
}

onMounted(() => {
	if (initData.value?.settings.can_edit) {
		loadStats();
		loadUpdateStatus();
		loadAdvisories();
	}
});
</script>
