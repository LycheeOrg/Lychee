<template>
	<UModal v-model:open="visible" :dismissible="false">
		<template #header>
			<span class="font-bold text-lg">{{ editingSize ? $t("webshop.sizeCatalogue.editPrintSize") : $t("webshop.sizeCatalogue.addPrintSize") }}</span>
		</template>
		<template #body>
			<div class="flex flex-col gap-4">
				<UFormField :label="`${$t('webshop.sizeCatalogue.label')} *`">
					<UInput id="print-label" v-model="form.label" class="w-full" />
				</UFormField>
				<div class="flex gap-2">
					<UFormField :label="`${$t('webshop.sizeCatalogue.width')} *`" class="w-full">
						<UInputNumber id="print-width" v-model="form.width" :min="1" class="w-full" />
					</UFormField>
					<UFormField :label="`${$t('webshop.sizeCatalogue.height')} *`" class="w-full">
						<UInputNumber id="print-height" v-model="form.height" :min="1" class="w-full" />
					</UFormField>
				</div>
				<UFormField :label="`${$t('webshop.sizeCatalogue.unit')} *`">
					<USelectMenu id="print-unit" v-model="form.unit" :items="unitOptions" class="w-full" />
				</UFormField>
				<UFormField :label="$t('webshop.sizeCatalogue.paperType')">
					<UInput id="print-paper-type" v-model="form.paper_type" class="w-full" />
				</UFormField>
				<div class="flex items-center gap-2">
					<UCheckbox v-model="form.is_active" id="print-active" />
					<label for="print-active">{{ $t("webshop.sizeCatalogue.active") }}</label>
				</div>
			</div>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton class="flex-1 justify-center" color="neutral" variant="soft" @click="visible = false">
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton class="flex-1 justify-center" :disabled="!canSubmit" @click="save">
					{{ $t("dialogs.button.save") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import ShopManagementService from "@/services/shop-management-service";

const props = defineProps<{
	editingSize: App.Http.Resources.Shop.PrintSizeResource | null;
}>();

const visible = defineModel<boolean>("visible", { default: false });
const emits = defineEmits<{ saved: [] }>();

const toast = useAppToast();
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
