<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ $t("settings.title") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<div class="text-center lg:hidden font-bold text-danger-700 py-3" v-html="$t('settings.small_screen')"></div>
	<Panel :pt:header:class="'hidden'" class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto w-full">
		<!-- Empty panel to keep the same layout as other settings pages -->
		<DataTable
			v-model:expandedRows="expandedRows"
			:value="purchasables"
			:loading="purchasables === undefined"
			class="mt-4"
			selectionMode="single"
			dataKey="purchasable_id"
		>
			<Column expander style="width: 5rem" />
			<Column>
				<template #body="slotProps">
					<img :src="slotProps.data.photo_url" class="h-8 w-8" v-if="slotProps.data.photo_url" />
					<img src="/img/placeholder.png" class="h-8 w-8" v-else />
				</template>
			</Column>
			<Column header="title">
				<template #body="slotProps">
					{{ slotProps.data.album_title }}
				</template>
			</Column>
			<Column header="Description" field="description"></Column>
			<template #expansion="slotProps">
				<div class="flax gap-4">
					<div class="w-full">
						{{ slotProps.data.owner_notes }}
					</div>
					<div class="w-full flex flex-col">
						<h5>Prices for {{ slotProps.data.photo_title ?? slotProps.data.album_title }}</h5>
						<div class="flex flex-row gap-4" v-for="price in slotProps.data.prices" :key="`${price.size_variant}-${price.license_type}`">
							<div>{{ price.size_variant }}</div>
							<div>{{ price.license_type }}</div>
							<div>{{ price.price }}</div>
						</div>
					</div>
				</div>
			</template>
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
import { ref } from "vue";

const purchasables = ref<undefined | App.Http.Resources.Shop.EditablePurchasableResource[]>(undefined);
const expandedRows = ref({});

ShopManagementService.list().then((response) => {
	purchasables.value = response.data;
});
</script>
