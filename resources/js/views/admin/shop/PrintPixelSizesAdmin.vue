<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>
		<template #center>
			{{ $t("webshop.sizeCatalogue.title") }}
		</template>
		<template #end> </template>
	</Toolbar>
	<div class="text-center lg:hidden font-bold text-danger-700 py-3" v-html="$t('settings.small_screen')"></div>
	<Panel :pt:header:class="'hidden'" class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto w-full">
		<Disclaimer />

		<!-- Print sizes -->
		<div class="mt-6">
			<div class="flex flex-row items-center justify-between mb-3">
				<h2 class="text-xl font-semibold">{{ $t("webshop.sizeCatalogue.printSizes") }}</h2>
				<Button
					icon="pi pi-plus"
					:label="$t('webshop.sizeCatalogue.addPrintSize')"
					size="small"
					class="border-none"
					@click="openCreatePrintDialog"
				/>
			</div>
			<DataTable :value="printSizes" :loading="printSizes === undefined" dataKey="id" class="border border-none rounded">
				<Column field="label" :header="$t('webshop.sizeCatalogue.label')" />
				<Column :header="$t('webshop.sizeCatalogue.dimensions')">
					<template #body="slotProps"> {{ slotProps.data.width }}×{{ slotProps.data.height }} {{ slotProps.data.unit }} </template>
				</Column>
				<Column field="paper_type" :header="$t('webshop.sizeCatalogue.paperType')" />
				<Column :header="$t('webshop.sizeCatalogue.active')">
					<template #body="slotProps">
						<i :class="slotProps.data.is_active ? 'pi pi-check text-green-500' : 'pi pi-times text-red-500'" />
					</template>
				</Column>
				<Column :header="$t('webshop.sizeCatalogue.actions')" body-class="text-right">
					<template #body="slotProps">
						<Button icon="pi pi-pencil" text size="small" class="border-none" @click="openEditPrintDialog(slotProps.data)" />
						<Button
							icon="pi pi-trash"
							text
							severity="danger"
							size="small"
							class="border-none"
							@click="openDeletePrintDialog(slotProps.data)"
						/>
					</template>
				</Column>
			</DataTable>
		</div>

		<!-- Pixel sizes -->
		<div class="mt-8">
			<div class="flex flex-row items-center justify-between mb-3">
				<h2 class="text-xl font-semibold">{{ $t("webshop.sizeCatalogue.pixelSizes") }}</h2>
				<Button
					icon="pi pi-plus"
					:label="$t('webshop.sizeCatalogue.addPixelSize')"
					size="small"
					class="border-none"
					@click="openCreatePixelDialog"
				/>
			</div>
			<DataTable :value="pixelSizes" :loading="pixelSizes === undefined" dataKey="id" class="border border-none rounded">
				<Column field="label" :header="$t('webshop.sizeCatalogue.label')" />
				<Column :header="$t('webshop.sizeCatalogue.dimensions')">
					<template #body="slotProps"> {{ slotProps.data.width }}×{{ slotProps.data.height }} px </template>
				</Column>
				<Column :header="$t('webshop.sizeCatalogue.active')">
					<template #body="slotProps">
						<i :class="slotProps.data.is_active ? 'pi pi-check text-green-500' : 'pi pi-times text-red-500'" />
					</template>
				</Column>
				<Column :header="$t('webshop.sizeCatalogue.actions')" body-class="text-right">
					<template #body="slotProps">
						<Button icon="pi pi-pencil" text size="small" class="border-none" @click="openEditPixelDialog(slotProps.data)" />
						<Button
							icon="pi pi-trash"
							text
							severity="danger"
							size="small"
							class="border-none"
							@click="openDeletePixelDialog(slotProps.data)"
						/>
					</template>
				</Column>
			</DataTable>
		</div>
	</Panel>

	<PrintSizeFormDialog v-model:visible="showPrintDialog" :editing-size="editingPrintSize" @saved="loadPrintSizes" />
	<PixelSizeFormDialog v-model:visible="showPixelDialog" :editing-size="editingPixelSize" @saved="loadPixelSizes" />
	<SizeDeleteDialog type="print" v-model:visible="showPrintDeleteDialog" :deleting-size="deletingPrintSize" @deleted="loadPrintSizes" />
	<SizeDeleteDialog type="pixel" v-model:visible="showPixelDeleteDialog" :deleting-size="deletingPixelSize" @deleted="loadPixelSizes" />
</template>

<script setup lang="ts">
import Toolbar from "primevue/toolbar";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import Panel from "primevue/panel";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import Disclaimer from "@/components/webshop/Disclaimer.vue";
import PrintSizeFormDialog from "@/components/forms/shop-management/PrintSizeFormDialog.vue";
import PixelSizeFormDialog from "@/components/forms/shop-management/PixelSizeFormDialog.vue";
import SizeDeleteDialog from "@/components/forms/shop-management/SizeDeleteDialog.vue";
import ShopManagementService from "@/services/shop-management-service";
import { onMounted, ref } from "vue";

const printSizes = ref<undefined | App.Http.Resources.Shop.PrintSizeResource[]>(undefined);
const pixelSizes = ref<undefined | App.Http.Resources.Shop.PixelSizeResource[]>(undefined);

const showPrintDialog = ref(false);
const showPixelDialog = ref(false);
const editingPrintSize = ref<App.Http.Resources.Shop.PrintSizeResource | null>(null);
const editingPixelSize = ref<App.Http.Resources.Shop.PixelSizeResource | null>(null);

const showPrintDeleteDialog = ref(false);
const showPixelDeleteDialog = ref(false);
const deletingPrintSize = ref<App.Http.Resources.Shop.PrintSizeResource | null>(null);
const deletingPixelSize = ref<App.Http.Resources.Shop.PixelSizeResource | null>(null);

function loadPrintSizes() {
	ShopManagementService.listPrintSizes()
		.then((response) => {
			printSizes.value = response.data;
		})
		.catch(() => {
			printSizes.value = [];
		});
}

function loadPixelSizes() {
	ShopManagementService.listPixelSizes()
		.then((response) => {
			pixelSizes.value = response.data;
		})
		.catch(() => {
			pixelSizes.value = [];
		});
}

function openCreatePrintDialog() {
	editingPrintSize.value = null;
	showPrintDialog.value = true;
}

function openEditPrintDialog(size: App.Http.Resources.Shop.PrintSizeResource) {
	editingPrintSize.value = size;
	showPrintDialog.value = true;
}

function openDeletePrintDialog(size: App.Http.Resources.Shop.PrintSizeResource) {
	deletingPrintSize.value = size;
	showPrintDeleteDialog.value = true;
}

function openCreatePixelDialog() {
	editingPixelSize.value = null;
	showPixelDialog.value = true;
}

function openEditPixelDialog(size: App.Http.Resources.Shop.PixelSizeResource) {
	editingPixelSize.value = size;
	showPixelDialog.value = true;
}

function openDeletePixelDialog(size: App.Http.Resources.Shop.PixelSizeResource) {
	deletingPixelSize.value = size;
	showPixelDeleteDialog.value = true;
}

onMounted(() => {
	loadPrintSizes();
	loadPixelSizes();
});
</script>
