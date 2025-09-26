<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ "Purchasables" }}
		</template>

		<template #end> </template>
	</Toolbar>
	<div class="text-center lg:hidden font-bold text-danger-700 py-3" v-html="$t('settings.small_screen')"></div>
	<Panel :pt:header:class="'hidden'" class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto w-full">
		<!-- Empty panel to keep the same layout as other settings pages -->
		<DataTable :value="purchasables" :loading="purchasables === undefined" class="mt-4" selectionMode="single" dataKey="purchasable_id">
			<Column header-class="w-1/12" body-class="w-1/12 align-top">
				<template #body="slotProps">
					<img :src="slotProps.data.photo_url" class="h-8 w-8" v-if="slotProps.data.photo_url" />
					<img src="/img/placeholder.png" class="h-8 w-8" v-else />
				</template>
			</Column>
			<Column header="Title" header-class="w-2/12" body-class="w-2/12 align-top">
				<template #body="slotProps">
					<router-link
						:to="{ name: 'album', params: { albumId: slotProps.data.album_id, photoId: slotProps.data.photo_id } }"
						target="_blank"
						class=""
					>
						{{ slotProps.data.photo_title ?? slotProps.data.album_title }}
					</router-link>
				</template>
			</Column>
			<Column header="Description" field="description" header-class="w-3/12" body-class="w-3/12 align-top text-sm"></Column>
			<Column header="Notes" field="owner_notes" header-class="w-2/12" body-class="w-2/12 align-top text-sm"></Column>
			<Column header="Prices" header-class="w-4/12" body-class="w-4/12 text-sm">
				<template #body="slotProps">
					<div class="w-full flex flex-col">
						<div
							class="flex flex-row gap-2 w-full"
							v-for="price in slotProps.data.prices"
							:key="`${price.size_variant}-${price.license_type}`"
						>
							<div class="w-1/3">{{ price.size_variant }}</div>
							<div class="w-1/3">{{ price.license_type }}</div>
							<div class="ltr:text-right rtl:text-left w-1/3">{{ price.price }}</div>
						</div>
					</div>
				</template>
			</Column>
		</DataTable>
	</Panel>
</template>

<script setup lang="ts">
import Toolbar from "primevue/toolbar";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import Panel from "primevue/panel";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import ShopManagementService from "@/services/shop-management-service";
import { onMounted, ref } from "vue";
import { RouterLink } from "vue-router";

const purchasables = ref<undefined | App.Http.Resources.Shop.EditablePurchasableResource[]>(undefined);

function load() {
	ShopManagementService.list().then((response) => {
		purchasables.value = response.data;
	});
}

onMounted(() => {
	load();
});
</script>
