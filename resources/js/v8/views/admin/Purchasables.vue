<template>
	<div class="w-full border-0 h-14 flex items-center justify-between px-2">
		<OpenLeftMenu />
		<span class="absolute left-1/2 -translate-x-1/2">{{ $t("webshop.purchasablesList.purchasables") }}</span>
	</div>
	<div class="text-center lg:hidden font-bold text-error py-3" v-html="$t('settings.small_screen')"></div>
	<UCard class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto w-full" :ui="{ header: 'hidden' }">
		<Disclaimer />
		<!-- Empty panel to keep the same layout as other settings pages -->
		<UTable :data="purchasables ?? []" :columns="columns" :loading="purchasables === undefined" class="mt-4" />
	</UCard>
</template>

<script setup lang="ts">
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import ShopManagementService from "@/services/shop-management-service";
import { h, onMounted, ref } from "vue";
import { RouterLink } from "vue-router";
import Disclaimer from "@/v8/components/webshop/Disclaimer.vue";
import { trans } from "laravel-vue-i18n";
import type { TableColumn } from "@nuxt/ui";

const purchasables = ref<undefined | App.Http.Resources.Shop.EditablePurchasableResource[]>(undefined);

const columns: TableColumn<App.Http.Resources.Shop.EditablePurchasableResource>[] = [
	{
		id: "photo",
		cell: ({ row }) =>
			row.original.photo_url
				? h("img", { src: row.original.photo_url, class: "h-8 w-8" })
				: h("img", { src: "/img/placeholder.png", class: "h-8 w-8" }),
	},
	{
		id: "title",
		header: trans("webshop.purchasablesList.title"),
		cell: ({ row }) =>
			h(
				RouterLink,
				{ to: { name: "album", params: { albumId: row.original.album_id, photoId: row.original.photo_id } }, target: "_blank" },
				() => row.original.photo_title ?? row.original.album_title,
			),
	},
	{ accessorKey: "description", header: trans("webshop.purchasablesList.description") },
	{ accessorKey: "owner_notes", header: trans("webshop.purchasablesList.notes") },
	{
		id: "prices",
		header: trans("webshop.purchasablesList.prices"),
		cell: ({ row }) =>
			h(
				"div",
				{ class: "w-full flex flex-col" },
				(row.original.prices ?? []).map((price) =>
					h("div", { key: `${price.size_variant}-${price.license_type}`, class: "flex flex-row gap-2 w-full" }, [
						h("div", { class: "w-1/3" }, price.size_variant),
						h("div", { class: "w-1/3" }, price.license_type),
						h("div", { class: "ltr:text-right rtl:text-left w-1/3" }, price.price),
					]),
				),
			),
	},
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
