<template>
	<div class="flex flex-col gap-2 w-full" v-if="printSizeOptions.length > 0">
		<div class="text-sm font-semibold mb-1">{{ $t("webshop.printSizePricesInput.title") }}</div>
		<div v-for="(item, index) in items" :key="`print-size-${index}`" class="flex flex-row gap-2 items-center">
			<div class="w-full">
				<Select
					v-model="item.print_size_id"
					:options="printSizeOptions"
					option-label="label"
					option-value="id"
					:placeholder="$t('webshop.printSizePricesInput.selectSize')"
					class="w-full border-b border-0"
				/>
			</div>
			<div class="w-full">
				<InputCurrency :value="item.price" :currency="currency" @update:model-value="(value: number) => (item.price = value)" />
			</div>
			<div>
				<Button
					class="py-2.5 border-0 text-danger-700 font-bold hover:text-white hover:bg-danger-800 bg-transparent"
					@click="items.splice(index, 1)"
				>
					<span class="pi pi-trash"></span>
				</Button>
			</div>
		</div>
		<Button
			:label="$t('webshop.printSizePricesInput.addSize')"
			icon="pi pi-plus"
			class="p-button-sm p-button-outlined"
			@click="items.push({ ..._defaultItem })"
		/>
	</div>
</template>

<script setup lang="ts">
import type { PrintSizeAssignment } from "@/services/shop-management-service";
import Button from "primevue/button";
import Select from "primevue/select";
import { onMounted, ref } from "vue";
import InputCurrency from "@/components/forms/basic/InputCurrency.vue";
import ShopManagementService from "@/services/shop-management-service";
import { useShopManagementStore } from "@/stores/ShopManagement";
import { storeToRefs } from "pinia";

const props = defineProps<{
	modelValue: PrintSizeAssignment[];
}>();

const emit = defineEmits<{
	(e: "update:modelValue", value: PrintSizeAssignment[]): void;
}>();

const ShopManagementStore = useShopManagementStore();
const { currency, default_price_cents } = storeToRefs(ShopManagementStore);

const printSizeOptions = ref<App.Http.Resources.Shop.PrintSizeResource[]>([]);
const items = ref<PrintSizeAssignment[]>(props.modelValue);

const _defaultItem: PrintSizeAssignment = { print_size_id: 0, price: default_price_cents.value };

function load() {
	ShopManagementService.listPrintSizes().then((response) => {
		printSizeOptions.value = response.data.filter((s) => s.is_active);
	});
	ShopManagementStore.init();
}

onMounted(() => {
	load();
});

// Emit updates when items change — parent binds via v-model
import { watch } from "vue";
watch(
	items,
	(value) => {
		emit("update:modelValue", value);
	},
	{ deep: true },
);
</script>
