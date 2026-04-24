<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ $t("maintenance.title") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<div class="text-muted-color text-center mt-2 p-2">
		{{ $t("maintenance.description") }}
	</div>
	<div
		class="md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 md:gap-8 lg:grid-cols-4 w-full"
	>
		<MaintenanceUpdate />
		<MaintenanceOptimize />
		<MaintenanceDuplicateChecker />
		<MaintenanceFlushCache />
		<MaintenanceGenSizevariants :sv="2" />
		<MaintenanceGenSizevariants :sv="3" />
		<MaintenanceGenSizevariants :sv="4" />
		<MaintenanceGenSizevariants :sv="5" />
		<MaintenanceGenSizevariants :sv="8" />
		<MaintenanceFixJobs />
		<MaintenanceFixTree />
		<MaintenanceFilesize />
		<MaintenanceOldOrders />
		<MaintenanceFulfillOrders />
		<MaintenanceFulfillPrecompute />
		<MaintenanceBackfillAlbumSizes />
		<MaintenanceFlushQueue />
		<MaintenanceMissingPalettes />
		<StatisticsIntegrity />
		<MaintenanceCleaning path="filesystems.disks.extract-jobs.root" />
		<MaintenanceCleaning path="filesystems.disks.image-jobs.root" />
		<MaintenanceCleaning path="filesystems.disks.image-upload.root" />
	</div>
</template>
<script setup lang="ts">
import Toolbar from "primevue/toolbar";
import MaintenanceCleaning from "@/components/maintenance/MaintenanceCleaning.vue";
import MaintenanceFilesize from "@/components/maintenance/MaintenanceFilesize.vue";
import MaintenanceFixJobs from "@/components/maintenance/MaintenanceFixJobs.vue";
import MaintenanceFixTree from "@/components/maintenance/MaintenanceFixTree.vue";
import MaintenanceDuplicateChecker from "@/components/maintenance/MaintenanceDuplicateChecker.vue";
import MaintenanceGenSizevariants from "@/components/maintenance/MaintenanceGenSizevariants.vue";
import MaintenanceOptimize from "@/components/maintenance/MaintenanceOptimize.vue";
import MaintenanceUpdate from "@/components/maintenance/MaintenanceUpdate.vue";
import MaintenanceFlushCache from "@/components/maintenance/MaintenanceFlushCache.vue";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import StatisticsIntegrity from "@/components/maintenance/StatisticsIntegrity.vue";
import MaintenanceMissingPalettes from "@/components/maintenance/MaintenanceMissingPalettes.vue";
import MaintenanceOldOrders from "@/components/maintenance/MaintenanceOldOrders.vue";
import MaintenanceFulfillOrders from "@/components/maintenance/MaintenanceFulfillOrders.vue";
import MaintenanceFulfillPrecompute from "@/components/maintenance/MaintenanceFulfillPrecompute.vue";
import MaintenanceBackfillAlbumSizes from "@/components/maintenance/MaintenanceBackfillAlbumSizes.vue";
import MaintenanceFlushQueue from "@/components/maintenance/MaintenanceFlushQueue.vue";
</script>
