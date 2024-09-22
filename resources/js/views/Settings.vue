<template>
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<router-link :to="{ name: 'gallery' }">
				<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="" />
			</router-link>
		</template>

		<template #center>
			{{ $t("lychee.SETTINGS") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Tabs value="0" class="max-w-5xl mx-auto">
		<TabList>
			<Tab value="0">Basic</Tab>
			<Tab value="1"><i class="pi pi-exclamation-triangle mr-2" />{{ $t("lychee.MORE") }}</Tab>
		</TabList>
		<TabPanels :pt:root:class="'px-0'">
			<TabPanel value="0">
				<span class="font-bold text-red-500 text-center w-full inline-block text-3xl">STILL WORK IN PROGRESS HERE (nothing works).</span>
				<EasySettings />
			</TabPanel>
			<TabPanel value="1">
				<AllSettings />
			</TabPanel>
		</TabPanels>
	</Tabs>
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Tabs from "primevue/tabs";
import TabList from "primevue/tablist";
import Tab from "primevue/tab";
import TabPanels from "primevue/tabpanels";
import TabPanel from "primevue/tabpanel";
import Toolbar from "primevue/toolbar";
import AllSettings from "@/components/settings/AllSettings.vue";
import EasySettings from "@/components/settings/EasySettings.vue";
</script>
