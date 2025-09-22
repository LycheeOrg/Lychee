<template>
	<div class="flex flex-col gap-2 w-full">
		<div v-for="(price, index) in pricesValues" :key="`price-${index}`" class="flex flex-row gap-2">
			<div class="w-full">
				<InputCurrency :value="price.price" @update:model-value="(value: number) => (pricesValues[index].price = value)" />
			</div>
			<div class="w-full">
				<Select v-model="price.license_type" :options="licenseTypeOptions" placeholder="License Type" class="w-full border-b border-0" />
			</div>
			<div class="w-full">
				<Select v-model="price.size_variant_type" :options="sizeVariantOptions" placeholder="Variant" class="w-full border-b border-0" />
			</div>
			<div>
				<Button icon="pi pi-trash" severity="danger" class="py-1.5 border-0" @click="pricesValues.splice(index, 1)" />
			</div>
		</div>
		<Message v-if="!isValid" severity="error">There are duplicate prices (same license type and size variant).</Message>
		<Button label="Add Price" icon="pi pi-plus" class="p-button-sm p-button-outlined" @click="pricesValues.push({ ..._priceDefault })" />
	</div>
</template>
<script setup lang="ts">
import { Price } from "@/services/shop-management-service";
import Button from "primevue/button";
import Select from "primevue/select";
import { computed, ref } from "vue";
import InputCurrency from "@/components/forms/basic/InputCurrency.vue";
import Message from "primevue/message";

const props = defineProps<{
	prices: Price[];
}>();

const pricesValues = ref<Price[]>(props.prices);
const licenseTypeOptions = ["personal", "commercial", "extended"];
const sizeVariantOptions = ["medium", "medium2x", "original", "full"];

const _priceDefault: Price = {
	price: 0,
	license_type: "personal",
	size_variant_type: "medium",
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

// function parseCurrencyAmount(str: string) : number | null {
//   const cleanedStr = str.replace(/[^\d.-]/g, '');
//   const amount = parseFloat(cleanedStr);
//   return isNaN(amount) ? null : amount;
// }

// function loadPrices(pricesList: App.Http.Resources.Shop.PriceResource[]) {
// 	for (let index = 0; index < pricesList.length; index++) {
// 		const element: PriceCandidate = {
// 			price: parseCurrencyAmount(pricesList[index].price),
// 			license_type: pricesList[index].license_type,
// 			size_variant: pricesList[index].size_variant,
// 		}
// 		pricesValues.value.push(element);
// 	}
// }

// onMounted(() => {
// 	if (props.prices && props.prices.length > 0) {
// 		loadPrices(props.prices);
// 	}
// });
</script>
