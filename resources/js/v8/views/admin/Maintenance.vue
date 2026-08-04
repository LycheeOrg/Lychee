<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("maintenance.title") }}
	</UHeader>
	<div class="text-muted text-center mt-2 p-2">
		{{ $t("maintenance.description") }}
	</div>
	<div class="max-w-7xl mt-9 mx-auto flex flex-col divide-y divide-default w-full px-4 sm:px-6">
		<MaintenanceUpdate />
		<MaintenanceOptimize />
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
		<MaintenanceSyncFaceEmbeddings v-if="initData?.modules.is_face_recognition_enabled" ref="syncFaceEmbeddingsRef" />
		<MaintenanceResetFaceScanStatus v-if="initData?.modules.is_face_recognition_enabled" />
		<MaintenancePurgeOrphanFaceEmbeddings v-if="initData?.modules.is_face_recognition_enabled" @purged="syncFaceEmbeddingsRef?.load()" />
	</div>
</template>
<script setup lang="ts">
import MaintenanceCleaning from "@/v8/components/maintenance/MaintenanceCleaning.vue";
import MaintenanceFilesize from "@/v8/components/maintenance/MaintenanceFilesize.vue";
import MaintenanceFixJobs from "@/v8/components/maintenance/MaintenanceFixJobs.vue";
import MaintenanceFixTree from "@/v8/components/maintenance/MaintenanceFixTree.vue";
import MaintenanceGenSizevariants from "@/v8/components/maintenance/MaintenanceGenSizevariants.vue";
import MaintenanceOptimize from "@/v8/components/maintenance/MaintenanceOptimize.vue";
import MaintenanceUpdate from "@/v8/components/maintenance/MaintenanceUpdate.vue";
import MaintenanceFlushCache from "@/v8/components/maintenance/MaintenanceFlushCache.vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import StatisticsIntegrity from "@/v8/components/maintenance/StatisticsIntegrity.vue";
import MaintenanceMissingPalettes from "@/v8/components/maintenance/MaintenanceMissingPalettes.vue";
import MaintenanceOldOrders from "@/v8/components/maintenance/MaintenanceOldOrders.vue";
import MaintenanceFulfillOrders from "@/v8/components/maintenance/MaintenanceFulfillOrders.vue";
import MaintenanceFulfillPrecompute from "@/v8/components/maintenance/MaintenanceFulfillPrecompute.vue";
import MaintenanceBackfillAlbumSizes from "@/v8/components/maintenance/MaintenanceBackfillAlbumSizes.vue";
import MaintenanceFlushQueue from "@/v8/components/maintenance/MaintenanceFlushQueue.vue";
import MaintenanceBulkScanFaces from "@/v8/components/maintenance/MaintenanceBulkScanFaces.vue";
import MaintenanceBulkScanNsfw from "@/v8/components/maintenance/MaintenanceBulkScanNsfw.vue";
import MaintenanceRunClustering from "@/v8/components/maintenance/MaintenanceRunClustering.vue";
import MaintenanceDestroyDismissedFaces from "@/v8/components/maintenance/MaintenanceDestroyDismissedFaces.vue";
import MaintenanceSyncFaceEmbeddings from "@/v8/components/maintenance/MaintenanceSyncFaceEmbeddings.vue";
import MaintenanceResetFaceScanStatus from "@/v8/components/maintenance/MaintenanceResetFaceScanStatus.vue";
import MaintenancePurgeOrphanFaceEmbeddings from "@/v8/components/maintenance/MaintenancePurgeOrphanFaceEmbeddings.vue";
import { storeToRefs } from "pinia";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useTemplateRef } from "vue";

const leftMenu = useLeftMenuStateStore();
const { initData } = storeToRefs(leftMenu);
const syncFaceEmbeddingsRef = useTemplateRef<InstanceType<typeof MaintenanceSyncFaceEmbeddings>>("syncFaceEmbeddingsRef");
</script>
