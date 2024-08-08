<template>
	<Toolbar class="w-full border-0">
		<template #start>
			<router-link :to="{ name: 'gallery' }">
				<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="" />
			</router-link>
		</template>

		<template #center>
			{{ $t("maintenance.title") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel class="border-none text-center text-muted-color">
		{{ $t('maintenance.description') }}
	</Panel>
	<div class="max-w-5xl mt-9 mx-auto grid grid-cols-1 gap-4 sm:grid-cols-2 sm:items-stretch md:grid-cols-3 md:gap-8 w-full">
		<MaintenanceUpdate />
	</div>
	<!--
	<livewire:modules.maintenance.optimize />
	<livewire:modules.maintenance.cleaning :path="config('filesystems.disks.extract-jobs.root')" />
	<livewire:modules.maintenance.cleaning :path="config('filesystems.disks.image-jobs.root')" />
	<livewire:modules.maintenance.cleaning :path="config('filesystems.disks.livewire-upload.root')" />
	<livewire:modules.maintenance.fix-jobs />
	<livewire:modules.maintenance.fix-tree />
	<livewire:modules.maintenance.gen-size-variants :type="\App\Enum\SizeVariantType::SMALL->value" />
	<livewire:modules.maintenance.gen-size-variants :type="\App\Enum\SizeVariantType::SMALL2X->value" />
	<livewire:modules.maintenance.gen-size-variants :type="\App\Enum\SizeVariantType::MEDIUM->value" />
	<livewire:modules.maintenance.gen-size-variants :type="\App\Enum\SizeVariantType::MEDIUM2X->value" />
	<livewire:modules.maintenance.missing-file-sizes />
	-->

</template>
<script setup lang="ts">
import MaintenanceUpdate from "@/components/maintenance/MaintenanceUpdate.vue";
import Button from "primevue/button";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";

</script>
