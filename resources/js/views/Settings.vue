<template>
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<router-link :to="{ name: 'gallery' }">
				<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text />
			</router-link>
		</template>

		<template #center>
			{{ $t("lychee.SETTINGS") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<div class="text-center sm:hidden font-bold text-danger-700 py-3">
		For better a experience on the Settings page,<br />
		we recommend you use a larger screen.
	</div>
	<Tabs value="0" class="max-w-5xl mx-auto">
		<TabList>
			<Tab value="0">Basic</Tab>
			<Tab value="1"><i class="pi pi-exclamation-triangle mr-2" />{{ $t("lychee.MORE") }}</Tab>
		</TabList>
		<TabPanels :pt:root:class="'px-0'">
			<TabPanel value="0">
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
