<template>
	<div class="flex flex-col gap-2 w-full" v-if="pixelSizeOptions.length > 0">
		<div class="text-sm font-semibold mb-1">{{ $t("webshop.pixelSizePricesInput.title") }}</div>
		<div v-for="(item, index) in items" :key="`pixel-size-${index}`" class="flex flex-row gap-2 items-center">
			<div class="w-full">
				<USelectMenu
					v-model="item.pixel_size_id"
					:items="pixelSizeOptions"
					label-key="label"
					value-key="id"
					:placeholder="$t('webshop.pixelSizePricesInput.selectSize')"
					class="w-full"
				/>
			</div>
			<div class="w-full">
				<USelectMenu v-model="item.license_type" :items="licenseTypeOptions" class="w-full" />
			</div>
			<div class="w-full">
				<InputCurrency :value="item.price" :currency="currency" @update:model-value="(value: number) => (item.price = value)" />
			</div>
			<div>
				<UButton color="error" variant="ghost" icon="prime:trash" @click="items.splice(index, 1)" />
			</div>
		</div>
		<UButton :label="$t('webshop.pixelSizePricesInput.addSize')" icon="prime:plus" variant="outline" size="sm" @click="items.push({ ..._defaultItem })" />
	</div>
</template>

<script setup lang="ts">
import type { PixelSizeAssignment } from "@/services/shop-management-service";
import { onMounted, ref, watch } from "vue";
import InputCurrency from "@/v8/components/forms/basic/InputCurrency.vue";
import ShopManagementService from "@/services/shop-management-service";
import { useShopManagementStore } from "@/stores/ShopManagement";
import { storeToRefs } from "pinia";

const props = defineProps<{
	modelValue: PixelSizeAssignment[];
}>();

const emit = defineEmits<{
	(e: "update:modelValue", value: PixelSizeAssignment[]): void;
}>();

const ShopManagementStore = useShopManagementStore();
const { currency, default_price_cents } = storeToRefs(ShopManagementStore);

const pixelSizeOptions = ref<App.Http.Resources.Shop.PixelSizeResource[]>([]);
const items = ref<PixelSizeAssignment[]>(props.modelValue);
const licenseTypeOptions: App.Enum.PurchasableLicenseType[] = ["personal", "commercial", "extended"];

const _defaultItem: PixelSizeAssignment = { pixel_size_id: 0, price: default_price_cents.value, license_type: "personal" };

function load() {
	ShopManagementService.listPixelSizes().then((response) => {
		pixelSizeOptions.value = response.data.filter((s) => s.is_active);
	});
	ShopManagementStore.init();
}

onMounted(() => {
	load();
});

watch(
	items,
	(value) => {
		emit("update:modelValue", value);
	},
	{ deep: true },
);
</script>
