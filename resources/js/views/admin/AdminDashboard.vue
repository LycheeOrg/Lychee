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
				@click="refreshStats"
			/>
		</template>
	</Toolbar>

	<div class="admin-dashboard max-w-7xl mx-auto p-4">
		<!-- Stats Overview (only for full admins with settings.can_edit) -->
		<section v-if="initData?.settings.can_edit" class="mb-8">
			<h2 class="text-xl font-semibold mb-4">{{ $t("admin-dashboard.overview") }}</h2>
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
		</section>

		<!-- Tools grid -->
		<section>
			<h2 class="text-xl font-semibold mb-4">{{ $t("admin-dashboard.tools") }}</h2>
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
						<i :class="tile.icon" class="text-2xl" />
						<span class="text-sm">{{ $t(tile.label) }}</span>
					</component>
				</template>
			</div>
		</section>
	</div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { useToast } from "primevue/usetoast";
import Toolbar from "primevue/toolbar";
import Button from "primevue/button";
import ProgressSpinner from "primevue/progressspinner";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import AdminStatsService from "@/services/admin-stats-service";
import { useAdminTiles, type AdminTile } from "@/composables/useAdminTiles";

export default defineComponent({
	name: "AdminDashboard",
	components: {
		Toolbar,
		Button,
		ProgressSpinner,
		OpenLeftMenu,
		RouterLink,
	},
	setup() {
		const lycheeStore = useLycheeStateStore();
		const leftMenuStore = useLeftMenuStateStore();
		const toast = useToast();
		const router = useRouter();

		const { initData } = storeToRefs(leftMenuStore);
		const stats = ref<App.Http.Resources.Models.AdminStatsResource | null>(null);
		const isLoading = ref(false);

		const tiles: AdminTile[] = useAdminTiles(lycheeStore, leftMenuStore);

		function loadStats() {
			isLoading.value = true;
			AdminStatsService.getStats(false)
				.then((response) => {
					stats.value = response.data;
					isLoading.value = false;
					if (response.data.errors.length > 0) {
						toast.add({ severity: "warn", summary: "Warning", detail: response.data.errors.join("; "), life: 5000 });
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
						toast.add({ severity: "warn", summary: "Warning", detail: response.data.errors.join("; "), life: 5000 });
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

		onMounted(() => {
			if (initData.value?.settings.can_edit) {
				loadStats();
			}
		});

		return {
			initData,
			stats,
			isLoading,
			tiles,
			refreshStats,
			formatBytes,
			navigateTile,
			RouterLink,
		};
	},
});
</script>
