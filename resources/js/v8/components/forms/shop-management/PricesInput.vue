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
				<USelectMenu
					v-model="price.license_type"
					:items="licenseTypeOptions"
					:placeholder="$t('webshop.pricesInput.licenseType')"
					class="w-full"
				/>
			</div>
			<div class="w-full">
				<USelectMenu
					v-model="price.size_variant_type"
					:items="sizeVariantOptions"
					:placeholder="$t('webshop.pricesInput.variant')"
					class="w-full"
				/>
			</div>
			<div>
				<UButton
					color="error"
					variant="ghost"
					icon="lucide:trash"
					@click="
						() => {
							pricesValues.splice(index, 1);
						}
					"
				/>
			</div>
		</div>
		<UAlert v-if="!isValid" color="error" :description="$t('webshop.pricesInput.duplicateError')" />
		<UButton
			:label="$t('webshop.pricesInput.addPrice')"
			icon="lucide:plus"
			variant="outline"
			size="sm"
			@click="
				() => {
					pricesValues.push({ ..._priceDefault });
				}
			"
		/>
	</div>
</template>
<script setup lang="ts">
import { Price } from "@/services/shop-management-service";
import { computed, onMounted, ref } from "vue";
import InputCurrency from "@/v8/components/forms/basic/InputCurrency.vue";
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
