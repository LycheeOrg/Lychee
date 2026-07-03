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
		<MaintenanceOldOrders v-if="initData?.modules.is_mod_webshop_enabled" />
		<MaintenanceFulfillOrders v-if="initData?.modules.is_mod_webshop_enabled" />
		<MaintenanceFulfillPrecompute />
		<MaintenanceBackfillAlbumSizes />
		<MaintenanceFlushQueue />
		<MaintenanceMissingPalettes />
		<StatisticsIntegrity />
		<MaintenanceCleaning path="filesystems.disks.extract-jobs.root" />
		<MaintenanceCleaning path="filesystems.disks.image-jobs.root" />
		<MaintenanceCleaning path="filesystems.disks.image-upload.root" />
		<MaintenanceBulkScanFaces v-if="initData?.modules.is_face_recognition_enabled" />
		<MaintenanceBulkScanNsfw v-if="initData?.modules.is_nsfw_classifier_enabled" />
		<MaintenanceRunClustering v-if="initData?.modules.is_face_recognition_enabled" />
		<MaintenanceDestroyDismissedFaces v-if="initData?.modules.is_face_recognition_enabled" />
		<MaintenanceSyncFaceEmbeddings v-if="initData?.modules.is_face_recognition_enabled" />
		<MaintenanceResetFaceScanStatus v-if="initData?.modules.is_face_recognition_enabled" />
	</div>
</template>
<script setup lang="ts">
import Toolbar from "primevue/toolbar";
import MaintenanceCleaning from "@/v7/components/maintenance/MaintenanceCleaning.vue";
import MaintenanceFilesize from "@/v7/components/maintenance/MaintenanceFilesize.vue";
import MaintenanceFixJobs from "@/v7/components/maintenance/MaintenanceFixJobs.vue";
import MaintenanceFixTree from "@/v7/components/maintenance/MaintenanceFixTree.vue";
import MaintenanceDuplicateChecker from "@/v7/components/maintenance/MaintenanceDuplicateChecker.vue";
import MaintenanceGenSizevariants from "@/v7/components/maintenance/MaintenanceGenSizevariants.vue";
import MaintenanceOptimize from "@/v7/components/maintenance/MaintenanceOptimize.vue";
import MaintenanceUpdate from "@/v7/components/maintenance/MaintenanceUpdate.vue";
import MaintenanceFlushCache from "@/v7/components/maintenance/MaintenanceFlushCache.vue";
import OpenLeftMenu from "@/v7/components/headers/OpenLeftMenu.vue";
import StatisticsIntegrity from "@/v7/components/maintenance/StatisticsIntegrity.vue";
import MaintenanceMissingPalettes from "@/v7/components/maintenance/MaintenanceMissingPalettes.vue";
import MaintenanceOldOrders from "@/v7/components/maintenance/MaintenanceOldOrders.vue";
import MaintenanceFulfillOrders from "@/v7/components/maintenance/MaintenanceFulfillOrders.vue";
import MaintenanceFulfillPrecompute from "@/v7/components/maintenance/MaintenanceFulfillPrecompute.vue";
import MaintenanceBackfillAlbumSizes from "@/v7/components/maintenance/MaintenanceBackfillAlbumSizes.vue";
import MaintenanceFlushQueue from "@/v7/components/maintenance/MaintenanceFlushQueue.vue";
import MaintenanceBulkScanFaces from "@/v7/components/maintenance/MaintenanceBulkScanFaces.vue";
import MaintenanceBulkScanNsfw from "@/v7/components/maintenance/MaintenanceBulkScanNsfw.vue";
import MaintenanceRunClustering from "@/v7/components/maintenance/MaintenanceRunClustering.vue";
import MaintenanceDestroyDismissedFaces from "@/v7/components/maintenance/MaintenanceDestroyDismissedFaces.vue";
import MaintenanceSyncFaceEmbeddings from "@/v7/components/maintenance/MaintenanceSyncFaceEmbeddings.vue";
import MaintenanceResetFaceScanStatus from "@/v7/components/maintenance/MaintenanceResetFaceScanStatus.vue";
import { storeToRefs } from "pinia";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";

const leftMenu = useLeftMenuStateStore();
const { initData } = storeToRefs(leftMenu);
</script>
