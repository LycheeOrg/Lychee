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
			<DataTable :value="printSizes" :loading="printSizes === undefined" dataKey="id" class="border rounded">
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
							@click="confirmDeletePrintSize(slotProps.data)"
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
			<DataTable :value="pixelSizes" :loading="pixelSizes === undefined" dataKey="id" class="border rounded">
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
							@click="confirmDeletePixelSize(slotProps.data)"
						/>
					</template>
				</Column>
			</DataTable>
		</div>
	</Panel>

	<!-- Print size create/edit dialog -->
	<Dialog
		v-model:visible="showPrintDialog"
		:modal="true"
		:closable="true"
		class="w-md"
		pt:root:class="border-none"
		pt:mask:style="backdrop-filter: blur(2px)"
	>
		<template #header>
			<span class="font-bold text-lg">{{
				editingPrintSize ? $t("webshop.sizeCatalogue.editPrintSize") : $t("webshop.sizeCatalogue.addPrintSize")
			}}</span>
		</template>
		<div class="flex flex-col gap-4 p-4">
			<FloatLabel variant="on">
				<InputText id="print-label" v-model="printForm.label" class="w-full" />
				<label for="print-label">{{ $t("webshop.sizeCatalogue.label") }} *</label>
			</FloatLabel>
			<div class="flex gap-2">
				<FloatLabel variant="on" class="w-full">
					<InputNumber id="print-width" v-model="printForm.width" :min="1" class="w-full" />
					<label for="print-width">{{ $t("webshop.sizeCatalogue.width") }} *</label>
				</FloatLabel>
				<FloatLabel variant="on" class="w-full">
					<InputNumber id="print-height" v-model="printForm.height" :min="1" class="w-full" />
					<label for="print-height">{{ $t("webshop.sizeCatalogue.height") }} *</label>
				</FloatLabel>
			</div>
			<FloatLabel variant="on">
				<Select id="print-unit" v-model="printForm.unit" :options="unitOptions" class="w-full border-b border-0" />
				<label for="print-unit">{{ $t("webshop.sizeCatalogue.unit") }} *</label>
			</FloatLabel>
			<FloatLabel variant="on">
				<InputText id="print-paper-type" v-model="printForm.paper_type" class="w-full" />
				<label for="print-paper-type">{{ $t("webshop.sizeCatalogue.paperType") }}</label>
			</FloatLabel>
			<div class="flex items-center gap-2">
				<Checkbox v-model="printForm.is_active" binary inputId="print-active" />
				<label for="print-active">{{ $t("webshop.sizeCatalogue.active") }}</label>
			</div>
		</div>
		<template #footer>
			<Button :label="$t('dialogs.button.cancel')" severity="secondary" @click="showPrintDialog = false" class="border-none" />
			<Button :label="$t('dialogs.button.save')" @click="savePrintSize" class="border-none" />
		</template>
	</Dialog>

	<!-- Pixel size create/edit dialog -->
	<Dialog
		v-model:visible="showPixelDialog"
		:modal="true"
		:closable="true"
		class="w-md"
		pt:root:class="border-none"
		pt:mask:style="backdrop-filter: blur(2px)"
	>
		<template #header>
			<span class="font-bold text-lg">{{
				editingPixelSize ? $t("webshop.sizeCatalogue.editPixelSize") : $t("webshop.sizeCatalogue.addPixelSize")
			}}</span>
		</template>
		<div class="flex flex-col gap-4 p-4">
			<FloatLabel variant="on">
				<InputText id="pixel-label" v-model="pixelForm.label" class="w-full" />
				<label for="pixel-label">{{ $t("webshop.sizeCatalogue.label") }} *</label>
			</FloatLabel>
			<div class="flex gap-2">
				<FloatLabel variant="on" class="w-full">
					<InputNumber id="pixel-width" v-model="pixelForm.width" :min="1" class="w-full" />
					<label for="pixel-width">{{ $t("webshop.sizeCatalogue.width") }} px *</label>
				</FloatLabel>
				<FloatLabel variant="on" class="w-full">
					<InputNumber id="pixel-height" v-model="pixelForm.height" :min="1" class="w-full" />
					<label for="pixel-height">{{ $t("webshop.sizeCatalogue.height") }} px *</label>
				</FloatLabel>
			</div>
			<div class="flex items-center gap-2">
				<Checkbox v-model="pixelForm.is_active" binary inputId="pixel-active" />
				<label for="pixel-active">{{ $t("webshop.sizeCatalogue.active") }}</label>
			</div>
		</div>
		<template #footer>
			<Button :label="$t('dialogs.button.cancel')" severity="secondary" @click="showPixelDialog = false" class="border-none" />
			<Button :label="$t('dialogs.button.save')" @click="savePixelSize" class="border-none" />
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import Toolbar from "primevue/toolbar";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import Panel from "primevue/panel";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import FloatLabel from "primevue/floatlabel";
import InputText from "@/components/forms/basic/InputText.vue";
import InputNumber from "primevue/inputnumber";
import Select from "primevue/select";
import Checkbox from "primevue/checkbox";
import Disclaimer from "@/components/webshop/Disclaimer.vue";
import ShopManagementService from "@/services/shop-management-service";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import { onMounted, ref } from "vue";
import { trans } from "laravel-vue-i18n";

const toast = useToast();
const confirm = useConfirm();

const printSizes = ref<undefined | App.Http.Resources.Shop.PrintSizeResource[]>(undefined);
const pixelSizes = ref<undefined | App.Http.Resources.Shop.PixelSizeResource[]>(undefined);

const showPrintDialog = ref(false);
const showPixelDialog = ref(false);
const editingPrintSize = ref<App.Http.Resources.Shop.PrintSizeResource | null>(null);
const editingPixelSize = ref<App.Http.Resources.Shop.PixelSizeResource | null>(null);

const unitOptions = ["cm", "inch"];

const printFormDefault = { label: "", width: 10, height: 15, unit: "cm", paper_type: "", is_active: true };
const pixelFormDefault = { label: "", width: 1920, height: 1080, is_active: true };

const printForm = ref({ ...printFormDefault });
const pixelForm = ref({ ...pixelFormDefault });

function loadPrintSizes() {
	ShopManagementService.listPrintSizes().then((response) => {
		printSizes.value = response.data;
	});
}

function loadPixelSizes() {
	ShopManagementService.listPixelSizes().then((response) => {
		pixelSizes.value = response.data;
	});
}

function openCreatePrintDialog() {
	editingPrintSize.value = null;
	printForm.value = { ...printFormDefault };
	showPrintDialog.value = true;
}

function openEditPrintDialog(size: App.Http.Resources.Shop.PrintSizeResource) {
	editingPrintSize.value = size;
	printForm.value = {
		label: size.label,
		width: size.width,
		height: size.height,
		unit: size.unit,
		paper_type: size.paper_type ?? "",
		is_active: size.is_active,
	};
	showPrintDialog.value = true;
}

function savePrintSize() {
	const promise = editingPrintSize.value
		? ShopManagementService.updatePrintSize({ ...printForm.value, print_size_id: editingPrintSize.value.id })
		: ShopManagementService.createPrintSize(printForm.value);

	promise
		.then(() => {
			showPrintDialog.value = false;
			loadPrintSizes();
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: trans("webshop.sizeCatalogue.error"), detail: error.message, life: 3000 });
		});
}

function confirmDeletePrintSize(size: App.Http.Resources.Shop.PrintSizeResource) {
	confirm.require({
		message: trans("webshop.sizeCatalogue.confirmDeleteMessage"),
		header: trans("webshop.sizeCatalogue.confirmDeleteHeader"),
		icon: "pi pi-exclamation-triangle",
		accept: () => {
			ShopManagementService.deletePrintSize(size.id)
				.then(() => loadPrintSizes())
				.catch((error) => {
					toast.add({ severity: "error", summary: trans("webshop.sizeCatalogue.error"), detail: error.message, life: 3000 });
				});
		},
	});
}

function openCreatePixelDialog() {
	editingPixelSize.value = null;
	pixelForm.value = { ...pixelFormDefault };
	showPixelDialog.value = true;
}

function openEditPixelDialog(size: App.Http.Resources.Shop.PixelSizeResource) {
	editingPixelSize.value = size;
	pixelForm.value = { label: size.label, width: size.width, height: size.height, is_active: size.is_active };
	showPixelDialog.value = true;
}

function savePixelSize() {
	const promise = editingPixelSize.value
		? ShopManagementService.updatePixelSize({ ...pixelForm.value, pixel_size_id: editingPixelSize.value.id })
		: ShopManagementService.createPixelSize(pixelForm.value);

	promise
		.then(() => {
			showPixelDialog.value = false;
			loadPixelSizes();
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: trans("webshop.sizeCatalogue.error"), detail: error.message, life: 3000 });
		});
}

function confirmDeletePixelSize(size: App.Http.Resources.Shop.PixelSizeResource) {
	confirm.require({
		message: trans("webshop.sizeCatalogue.confirmDeleteMessage"),
		header: trans("webshop.sizeCatalogue.confirmDeleteHeader"),
		icon: "pi pi-exclamation-triangle",
		accept: () => {
			ShopManagementService.deletePixelSize(size.id)
				.then(() => loadPixelSizes())
				.catch((error) => {
					toast.add({ severity: "error", summary: trans("webshop.sizeCatalogue.error"), detail: error.message, life: 3000 });
				});
		},
	});
}

onMounted(() => {
	loadPrintSizes();
	loadPixelSizes();
});
</script>
