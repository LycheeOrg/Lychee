<template>
	<Dialog v-model:visible="visible" modal :closable="false" pt:root:class="border-none m-3" pt:mask:style="backdrop-filter: blur(2px)">
		<template #container>
			<div class="flex flex-col gap-4 relative max-w-lg w-full rounded-md">
				<div class="flex items-center justify-between px-6 pt-6">
					<span class="font-bold text-lg">{{
						editingSize ? $t("webshop.sizeCatalogue.editPrintSize") : $t("webshop.sizeCatalogue.addPrintSize")
					}}</span>
					<button class="text-muted-color hover:text-primary-400 transition-colors" @click="visible = false">
						<i class="pi pi-times" />
					</button>
				</div>
				<div class="flex flex-col gap-4 px-6 pb-2">
					<FloatLabel variant="on">
						<InputText id="print-label" v-model="form.label" class="w-full" />
						<label for="print-label">{{ $t("webshop.sizeCatalogue.label") }} *</label>
					</FloatLabel>
					<div class="flex gap-2">
						<FloatLabel variant="on" class="w-full">
							<InputNumber id="print-width" v-model="form.width" :min="1" fluid />
							<label for="print-width">{{ $t("webshop.sizeCatalogue.width") }} *</label>
						</FloatLabel>
						<FloatLabel variant="on" class="w-full">
							<InputNumber id="print-height" v-model="form.height" :min="1" fluid />
							<label for="print-height">{{ $t("webshop.sizeCatalogue.height") }} *</label>
						</FloatLabel>
					</div>
					<FloatLabel variant="on">
						<Select id="print-unit" v-model="form.unit" :options="unitOptions" class="w-full border-b border-0" />
						<label for="print-unit">{{ $t("webshop.sizeCatalogue.unit") }} *</label>
					</FloatLabel>
					<FloatLabel variant="on">
						<InputText id="print-paper-type" v-model="form.paper_type" class="w-full" />
						<label for="print-paper-type">{{ $t("webshop.sizeCatalogue.paperType") }}</label>
					</FloatLabel>
					<div class="flex items-center gap-2">
						<Checkbox v-model="form.is_active" binary inputId="print-active" />
						<label for="print-active">{{ $t("webshop.sizeCatalogue.active") }}</label>
					</div>
				</div>
				<div class="flex items-center">
					<Button
						severity="secondary"
						class="w-full font-bold border-none rounded-none ltr:rounded-bl-xl rtl:rounded-br-xl shrink"
						@click="visible = false"
					>
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button
						class="w-full font-bold border-none rounded-none ltr:rounded-br-xl rtl:rounded-bl-xl shrink"
						@click="save"
						:disabled="!canSubmit"
					>
						{{ $t("dialogs.button.save") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import FloatLabel from "primevue/floatlabel";
import InputText from "@/components/forms/basic/InputText.vue";
import InputNumber from "primevue/inputnumber";
import Select from "primevue/select";
import Checkbox from "primevue/checkbox";
import ShopManagementService from "@/services/shop-management-service";

const props = defineProps<{
	editingSize: App.Http.Resources.Shop.PrintSizeResource | null;
}>();

const visible = defineModel<boolean>("visible", { default: false });
const emits = defineEmits<{ saved: [] }>();

const toast = useToast();
const unitOptions = ["cm", "inch"];

const defaultForm = () => ({ label: "", width: 10, height: 15, unit: "cm", paper_type: "", is_active: true });
const form = ref(defaultForm());

const canSubmit = computed(() => form.value.label.trim() !== "" && form.value.width > 0 && form.value.height > 0 && form.value.unit.trim() !== "");
function save() {
	const promise = props.editingSize
		? ShopManagementService.updatePrintSize({ ...form.value, print_size_id: props.editingSize.id })
		: ShopManagementService.createPrintSize(form.value);

	promise
		.then(() => {
			visible.value = false;
			emits("saved");
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: trans("webshop.sizeCatalogue.error"), detail: error.message, life: 3000 });
		});
}

watch(visible, (isVisible) => {
	if (!isVisible) return;
	form.value = props.editingSize
		? {
				label: props.editingSize.label,
				width: props.editingSize.width,
				height: props.editingSize.height,
				unit: props.editingSize.unit,
				paper_type: props.editingSize.paper_type ?? "",
				is_active: props.editingSize.is_active,
			}
		: defaultForm();
});
</script>
