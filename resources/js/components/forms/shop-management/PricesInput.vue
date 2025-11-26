<template>
	<div class="flex flex-col gap-2 w-full">
		<div v-for="(price, index) in pricesValues" :key="`price-${index}`" class="flex flex-row gap-2">
			<div class="w-full">
				<InputCurrency
					:value="price.price"
					:currency="currency"
					@update:model-value="(value: number) => (pricesValues[index].price = value)"
				/>
			</div>
			<div class="w-full">
				<Select
					v-model="price.license_type"
					:options="licenseTypeOptions"
					:placeholder="$t('webshop.pricesInput.licenseType')"
					class="w-full border-b border-0"
				/>
			</div>
			<div class="w-full">
				<Select
					v-model="price.size_variant_type"
					:options="sizeVariantOptions"
					:placeholder="$t('webshop.pricesInput.variant')"
					class="w-full border-b border-0"
				/>
			</div>
			<div>
				<Button
					class="py-2.5 border-0 text-danger-700 font-bold hover:text-white hover:bg-danger-800 bg-transparent"
					@click="pricesValues.splice(index, 1)"
				>
					<span class="pi pi-trash"></span>
				</Button>
			</div>
		</div>
		<Message v-if="!isValid" severity="error">{{ $t("webshop.pricesInput.duplicateError") }}</Message>
		<Button
			:label="$t('webshop.pricesInput.addPrice')"
			icon="pi pi-plus"
			class="p-button-sm p-button-outlined"
			@click="pricesValues.push({ ..._priceDefault })"
		/>
	</div>
</template>
<script setup lang="ts">
import { Price } from "@/services/shop-management-service";
import Button from "primevue/button";
import Select from "primevue/select";
import { computed, onMounted, ref } from "vue";
import InputCurrency from "@/components/forms/basic/InputCurrency.vue";
import Message from "primevue/message";
import { useShopManagementStore } from "@/stores/ShopManagement";
import { storeToRefs } from "pinia";

const props = defineProps<{
	prices: Price[];
}>();

const ShopManagementStore = useShopManagementStore();

const { currency, default_price_cents, default_license, default_size } = storeToRefs(ShopManagementStore);

const pricesValues = ref<Price[]>(props.prices);
const licenseTypeOptions = ["personal", "commercial", "extended"];
const sizeVariantOptions = ["medium", "medium2x", "original", "full"];

const _priceDefault: Price = {
	price: default_price_cents.value,
	license_type: default_license.value,
	size_variant_type: default_size.value,
};

// A price list is not valid if there is a duplicate (same license type and size variant).
const isValid = computed(() => {
	if (pricesValues.value.length === 0) {
		return true;
	}
	const duplicates: { [key: string]: boolean } = {};
	for (let index = 0; index < pricesValues.value.length; index++) {
		const element = pricesValues.value[index];
		const key = `${element.license_type}-${element.size_variant_type}`;
		if (duplicates[key]) {
			return false;
		}
		duplicates[key] = true;
	}

	return true;
});

function load() {
	ShopManagementStore.init();
}

onMounted(() => {
	load();
});
</script>
