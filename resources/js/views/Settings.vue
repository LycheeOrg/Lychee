<template>
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ $t("settings.title") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<div class="text-center sm:hidden font-bold text-danger-700 py-3" v-html="$t('settings.small_screen')" />
	<Tabs value="0" class="max-w-5xl mx-auto">
		<TabList>
			<Tab value="0">{{ $t("settings.tabs.basic") }}</Tab>
			<Tab value="1"><i class="pi pi-exclamation-triangle mr-2 text-orange-500" />{{ $t("settings.tabs.all_settings") }}</Tab>
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
	<ScrollTop :threshold="50" />
</template>
<script setup lang="ts">
import Tabs from "primevue/tabs";
import TabList from "primevue/tablist";
import Tab from "primevue/tab";
import TabPanels from "primevue/tabpanels";
import TabPanel from "primevue/tabpanel";
import Toolbar from "primevue/toolbar";
import AllSettings from "@/components/settings/AllSettings.vue";
import EasySettings from "@/components/settings/EasySettings.vue";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import ScrollTop from "primevue/scrolltop";
</script>
<style lang="css">
/* Kill the border of ScrollTop */
.p-scrolltop {
	border: none;
}
</style>
