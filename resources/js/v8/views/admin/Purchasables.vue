<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("webshop.purchasablesList.purchasables") }}
	</UHeader>
	<div class="text-center lg:hidden font-bold text-error py-3" v-html="$t('settings.small_screen')"></div>
	<UCard class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto w-full" :ui="{ header: 'hidden' }">
		<Disclaimer />
		<!-- Empty panel to keep the same layout as other settings pages -->
		<UTable
			:data="purchasables ?? []"
			:columns="columns"
			:loading="purchasables === undefined"
			:ui="{ td: 'px-2 py-1', th: 'px-2' }"
			class="mt-4"
		>
			<template #photo-cell="{ row }">
				<img :src="row.original.photo_url ?? '/img/placeholder.png'" class="h-8 w-8" />
			</template>
			<template #title-cell="{ row }">
				<RouterLink :to="{ name: 'album', params: { albumId: row.original.album_id, photoId: row.original.photo_id } }" target="_blank">
					{{ row.original.photo_title ?? row.original.album_title }}
				</RouterLink>
			</template>
			<template #prices-cell="{ row }">
				<div class="w-full flex flex-col">
					<div
						v-for="price in row.original.prices ?? []"
						:key="`${price.size_variant}-${price.license_type}`"
						class="flex flex-row gap-2 w-full"
					>
						<div class="w-1/3">{{ price.size_variant }}</div>
						<div class="w-1/3">{{ price.license_type }}</div>
						<div class="w-1/3 ltr:text-right rtl:text-left">{{ price.price }}</div>
					</div>
				</div>
			</template>
		</UTable>
	</UCard>
</template>

<script setup lang="ts">
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import ShopManagementService from "@/services/shop-management-service";
import { onMounted, ref } from "vue";
import { RouterLink } from "vue-router";
import Disclaimer from "@/v8/components/webshop/Disclaimer.vue";
import { trans } from "laravel-vue-i18n";
import type { TableColumn } from "@nuxt/ui";

const purchasables = ref<undefined | App.Http.Resources.Shop.EditablePurchasableResource[]>(undefined);

const columns: TableColumn<App.Http.Resources.Shop.EditablePurchasableResource>[] = [
	{ id: "photo" },
	{ id: "title", header: trans("webshop.purchasablesList.title") },
	{ accessorKey: "description", header: trans("webshop.purchasablesList.description") },
	{ accessorKey: "owner_notes", header: trans("webshop.purchasablesList.notes") },
	{ id: "prices", header: trans("webshop.purchasablesList.prices") },
];

function load() {
	ShopManagementService.list().then((response) => {
		purchasables.value = response.data;
	});
}

onMounted(() => {
	load();
});
</script>
