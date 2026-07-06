<template>
	<div class="flex flex-col gap-2 w-full" v-if="printSizeOptions.length > 0">
		<div class="text-sm font-semibold mb-1">{{ $t("webshop.printSizePricesInput.title") }}</div>
		<div v-for="(item, index) in items" :key="`print-size-${index}`" class="flex flex-row gap-2 items-center">
			<div class="w-full">
				<USelectMenu
					v-model="item.print_size_id"
					:items="printSizeOptions"
					label-key="label"
					value-key="id"
					:placeholder="$t('webshop.printSizePricesInput.selectSize')"
					class="w-full"
				/>
			</div>
			<div class="w-full">
				<InputCurrency :value="item.price" :currency="currency" @update:model-value="(value: number) => (item.price = value)" />
			</div>
			<div>
				<UButton
					color="error"
					variant="ghost"
					icon="prime:trash"
					@click="
						() => {
							items.splice(index, 1);
						}
					"
				/>
			</div>
		</div>
		<UButton
			:label="$t('webshop.printSizePricesInput.addSize')"
			icon="prime:plus"
			variant="outline"
			size="sm"
			@click="
				() => {
					items.push({ ..._defaultItem });
				}
			"
		/>
	</div>
</template>

<script setup lang="ts">
import type { PrintSizeAssignment } from "@/services/shop-management-service";
import { onMounted, ref, watch } from "vue";
import InputCurrency from "@/v8/components/forms/basic/InputCurrency.vue";
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

watch(
	items,
	(value) => {
		emit("update:modelValue", value);
	},
	{ deep: true },
);
</script>
