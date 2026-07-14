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
				<div class="bg-elevated rounded p-4 text-center">
					<div class="text-2xl font-bold">{{ stats.photos_count.toLocaleString() }}</div>
					<div class="text-muted text-sm">{{ $t("admin-dashboard.metrics.photos_count") }}</div>
				</div>
				<div class="bg-elevated rounded p-4 text-center">
					<div class="text-2xl font-bold">{{ stats.albums_count.toLocaleString() }}</div>
					<div class="text-muted text-sm">{{ $t("admin-dashboard.metrics.albums_count") }}</div>
				</div>
				<div class="bg-elevated rounded p-4 text-center">
					<div class="text-2xl font-bold">{{ stats.users_count.toLocaleString() }}</div>
					<div class="text-muted text-sm">{{ $t("admin-dashboard.metrics.users_count") }}</div>
				</div>
				<div class="bg-elevated rounded p-4 text-center">
					<div class="text-2xl font-bold">{{ formatBytes(stats.storage_bytes) }}</div>
					<div class="text-muted text-sm">{{ $t("admin-dashboard.metrics.storage_bytes") }}</div>
				</div>
				<div class="bg-elevated rounded p-4 text-center">
					<div class="text-2xl font-bold">{{ stats.queued_jobs }}</div>
					<div class="text-muted text-sm">{{ $t("admin-dashboard.metrics.queued_jobs") }}</div>
				</div>
				<div class="bg-elevated rounded p-4 text-center">
					<div class="text-2xl font-bold">{{ stats.failed_jobs_24h }}</div>
					<div class="text-muted text-sm">{{ $t("admin-dashboard.metrics.failed_jobs_24h") }}</div>
				</div>
				<div class="bg-elevated rounded p-4 text-center col-span-2">
					<div class="text-lg font-bold">{{ stats.last_successful_job_at ?? "—" }}</div>
					<div class="text-muted text-sm">{{ $t("admin-dashboard.metrics.last_successful_job_at") }}</div>
				</div>
			</div>
			<div v-if="stats && stats.errors.length > 0" class="mt-2 text-orange-500 text-sm">
				{{ $t("admin-dashboard.errors.partial") }}
			</div>
			<div v-if="!stats && !isLoading" class="text-muted text-center py-4">
				<Spinner class="text-2xl" />
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
						<template v-for="tile in section.tiles" :key="tile.key">
							<component
								:is="tile.isExternal ? 'a' : RouterLink"
								:to="tile.isExternal ? undefined : tile.to"
								:href="tile.isExternal ? tile.to : undefined"
								:target="tile.isExternal ? '_blank' : undefined"
								class="bg-elevated hover:bg-accented rounded p-4 text-center flex flex-col items-center gap-2 cursor-pointer no-underline text-default"
								tabindex="0"
								@keydown.enter="navigateTile(tile)"
								@keydown.space.prevent="navigateTile(tile)"
							>
								<UChip v-if="tile.num && tile.num.value > 0" :text="tile.num.value" color="primary">
									<PiMiniIcon :icon="tile.icon" class="w-6 h-6 text-2xl fill-white" />
								</UChip>
								<PiMiniIcon v-else :icon="tile.icon" class="w-6 h-6 text-2xl fill-white" />
								<span class="text-sm">{{ $t(tile.label) }}</span>
							</component>
						</template>
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
import { RouterLink, useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import Spinner from "@/v8/components/Spinner.vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import PiMiniIcon from "@/v8/components/icons/PiMiniIcon.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import Constants from "@/services/constants";
import SecurityAdvisoriesService from "@/services/security-advisories-service";
import AdminStatsService, { type AdminUpdateStatusResource } from "@/services/admin-stats-service";
import { useAdminTiles, type AdminTile, type AdminTileGroup } from "@/v8/composables/useAdminTiles";

const lycheeStore = useLycheeStateStore();
const leftMenuStore = useLeftMenuStateStore();
const toast = useAppToast();
const router = useRouter();

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
