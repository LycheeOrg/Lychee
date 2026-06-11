<template>
	<Dialog v-model:visible="visible" modal :closable="false" pt:root:class="border-none m-3" pt:mask:style="backdrop-filter: blur(2px)">
		<template #container>
			<div class="flex flex-col gap-4 relative max-w-md w-full rounded-md">
				<div class="flex items-center justify-between px-6 pt-6">
					<span class="font-bold text-lg">{{
						editingSize ? $t("webshop.sizeCatalogue.editPixelSize") : $t("webshop.sizeCatalogue.addPixelSize")
					}}</span>
					<button class="text-muted-color hover:text-primary-400 transition-colors" @click="visible = false">
						<i class="pi pi-times" />
					</button>
				</div>
				<div class="flex flex-col gap-4 px-6 pb-2">
					<FloatLabel variant="on">
						<InputText id="pixel-label" v-model="form.label" class="w-full" />
						<label for="pixel-label">{{ $t("webshop.sizeCatalogue.label") }} *</label>
					</FloatLabel>
					<div class="flex gap-2">
						<FloatLabel variant="on" class="w-full">
							<InputNumber id="pixel-width" v-model="form.width" :min="1" fluid />
							<label for="pixel-width">{{ $t("webshop.sizeCatalogue.width") }} px *</label>
						</FloatLabel>
						<FloatLabel variant="on" class="w-full">
							<InputNumber id="pixel-height" v-model="form.height" :min="1" fluid />
							<label for="pixel-height">{{ $t("webshop.sizeCatalogue.height") }} px *</label>
						</FloatLabel>
					</div>
					<div class="flex items-center gap-2">
						<Checkbox v-model="form.is_active" binary inputId="pixel-active" />
						<label for="pixel-active">{{ $t("webshop.sizeCatalogue.active") }}</label>
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
import Checkbox from "primevue/checkbox";
import ShopManagementService from "@/services/shop-management-service";

const props = defineProps<{
	editingSize: App.Http.Resources.Shop.PixelSizeResource | null;
}>();

const visible = defineModel<boolean>("visible", { default: false });
const emits = defineEmits<{ saved: [] }>();

const toast = useToast();

const defaultForm = () => ({ label: "", width: 1920, height: 1080, is_active: true });
const form = ref(defaultForm());

const canSubmit = computed(() => form.value.label.trim() !== "" && form.value.width > 0 && form.value.height > 0);

function save() {
	const promise = props.editingSize
		? ShopManagementService.updatePixelSize({ ...form.value, pixel_size_id: props.editingSize.id })
		: ShopManagementService.createPixelSize(form.value);

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
		? { label: props.editingSize.label, width: props.editingSize.width, height: props.editingSize.height, is_active: props.editingSize.is_active }
		: defaultForm();
});
</script>
