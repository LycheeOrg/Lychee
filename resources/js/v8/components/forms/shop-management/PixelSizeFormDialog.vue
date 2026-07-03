<template>
	<UModal v-model:open="visible" :dismissible="false">
		<template #header>
			<span class="font-bold text-lg">{{ editingSize ? $t("webshop.sizeCatalogue.editPixelSize") : $t("webshop.sizeCatalogue.addPixelSize") }}</span>
		</template>
		<template #body>
			<div class="flex flex-col gap-4">
				<UFormField :label="`${$t('webshop.sizeCatalogue.label')} *`">
					<UInput id="pixel-label" v-model="form.label" class="w-full" />
				</UFormField>
				<div class="flex gap-2">
					<UFormField :label="`${$t('webshop.sizeCatalogue.width')} px *`" class="w-full">
						<UInputNumber id="pixel-width" v-model="form.width" :min="1" class="w-full" />
					</UFormField>
					<UFormField :label="`${$t('webshop.sizeCatalogue.height')} px *`" class="w-full">
						<UInputNumber id="pixel-height" v-model="form.height" :min="1" class="w-full" />
					</UFormField>
				</div>
				<div class="flex items-center gap-2">
					<UCheckbox v-model="form.is_active" id="pixel-active" />
					<label for="pixel-active">{{ $t("webshop.sizeCatalogue.active") }}</label>
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
	editingSize: App.Http.Resources.Shop.PixelSizeResource | null;
}>();

const visible = defineModel<boolean>("visible", { default: false });
const emits = defineEmits<{ saved: [] }>();

const toast = useAppToast();

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
