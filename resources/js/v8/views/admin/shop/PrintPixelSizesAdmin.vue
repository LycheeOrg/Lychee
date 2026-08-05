<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("webshop.sizeCatalogue.title") }}
	</UHeader>
	<div class="text-center lg:hidden font-bold text-error py-3" v-html="$t('settings.small_screen')"></div>
	<UCard class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto w-full" :ui="{ header: 'hidden' }">
		<Disclaimer />

		<!-- Print sizes -->
		<div class="mt-6">
			<div class="flex flex-row items-center justify-between mb-3">
				<h2 class="text-xl font-semibold">{{ $t("webshop.sizeCatalogue.printSizes") }}</h2>
				<UButton
					color="primary"
					variant="solid"
					icon="lucide:plus"
					:label="$t('webshop.sizeCatalogue.addPrintSize')"
					size="sm"
					@click="openCreatePrintDialog"
				/>
			</div>
			<UTable :data="printSizes ?? []" :columns="printColumns" :loading="printSizes === undefined" :ui="{ td: 'px-2 py-1', th: 'px-2' }">
				<template #dimensions-cell="{ row }"> {{ row.original.width }}×{{ row.original.height }} {{ row.original.unit }} </template>
				<template #active-cell="{ row }">
					<Icon
						:icon="row.original.is_active ? 'lucide:check' : 'lucide:x'"
						:class="row.original.is_active ? 'text-success' : 'text-error'"
					/>
				</template>
				<template #actions-cell="{ row }">
					<div class="flex justify-end gap-1">
						<UButton icon="lucide:pencil" variant="ghost" color="neutral" size="sm" @click="openEditPrintDialog(row.original)" />
						<UButton icon="lucide:trash" variant="ghost" color="error" size="sm" @click="openDeletePrintDialog(row.original)" />
					</div>
				</template>
			</UTable>
		</div>

		<!-- Pixel sizes -->
		<div class="mt-8">
			<div class="flex flex-row items-center justify-between mb-3">
				<h2 class="text-xl font-semibold">{{ $t("webshop.sizeCatalogue.pixelSizes") }}</h2>
				<UButton
					color="primary"
					variant="solid"
					icon="lucide:plus"
					:label="$t('webshop.sizeCatalogue.addPixelSize')"
					size="sm"
					@click="openCreatePixelDialog"
				/>
			</div>
			<UTable :data="pixelSizes ?? []" :columns="pixelColumns" :loading="pixelSizes === undefined" :ui="{ td: 'px-2 py-1', th: 'px-2' }">
				<template #dimensions-cell="{ row }"> {{ row.original.width }}×{{ row.original.height }} px </template>
				<template #active-cell="{ row }">
					<Icon
						:icon="row.original.is_active ? 'lucide:check' : 'lucide:x'"
						:class="row.original.is_active ? 'text-success' : 'text-error'"
					/>
				</template>
				<template #actions-cell="{ row }">
					<div class="flex justify-end gap-1">
						<UButton icon="lucide:pencil" variant="ghost" color="neutral" size="sm" @click="openEditPixelDialog(row.original)" />
						<UButton icon="lucide:trash" variant="ghost" color="error" size="sm" @click="openDeletePixelDialog(row.original)" />
					</div>
				</template>
			</UTable>
		</div>
	</UCard>

	<PrintSizeFormDialog v-model:visible="showPrintDialog" :editing-size="editingPrintSize" @saved="loadPrintSizes" />
	<PixelSizeFormDialog v-model:visible="showPixelDialog" :editing-size="editingPixelSize" @saved="loadPixelSizes" />
	<SizeDeleteDialog type="print" v-model:visible="showPrintDeleteDialog" :deleting-size="deletingPrintSize" @deleted="loadPrintSizes" />
	<SizeDeleteDialog type="pixel" v-model:visible="showPixelDeleteDialog" :deleting-size="deletingPixelSize" @deleted="loadPixelSizes" />
</template>

<script setup lang="ts">
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import Disclaimer from "@/v8/components/webshop/Disclaimer.vue";
import PrintSizeFormDialog from "@/v8/components/forms/shop-management/PrintSizeFormDialog.vue";
import PixelSizeFormDialog from "@/v8/components/forms/shop-management/PixelSizeFormDialog.vue";
import SizeDeleteDialog from "@/v8/components/forms/shop-management/SizeDeleteDialog.vue";
import ShopManagementService from "@/services/shop-management-service";
import { onMounted, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import type { TableColumn } from "@nuxt/ui";
import UButton from "@nuxt/ui/components/Button.vue";
import { Icon } from "@iconify/vue";

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

const printColumns: TableColumn<App.Http.Resources.Shop.PrintSizeResource>[] = [
	{ accessorKey: "label", header: trans("webshop.sizeCatalogue.label") },
	{ id: "dimensions", header: trans("webshop.sizeCatalogue.dimensions") },
	{ accessorKey: "paper_type", header: trans("webshop.sizeCatalogue.paperType") },
	{ id: "active", header: trans("webshop.sizeCatalogue.active") },
	{ id: "actions", header: trans("webshop.sizeCatalogue.actions"), meta: { class: { th: "text-right", td: "text-right" } } },
];

const pixelColumns: TableColumn<App.Http.Resources.Shop.PixelSizeResource>[] = [
	{ accessorKey: "label", header: trans("webshop.sizeCatalogue.label") },
	{ id: "dimensions", header: trans("webshop.sizeCatalogue.dimensions") },
	{ id: "active", header: trans("webshop.sizeCatalogue.active") },
	{ id: "actions", header: trans("webshop.sizeCatalogue.actions"), meta: { class: { th: "text-right", td: "text-right" } } },
];

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
