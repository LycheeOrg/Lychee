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
	<div class="text-center lg:hidden font-bold text-danger-700 py-3" v-html="$t('settings.small_screen')"></div>
	<div class="pl-[calc(100vw-100%)] flex justify-center pb-10">
		<div class="max-w-6xl w-full">
			<ConfirmSave
				:is-save-visible="modified.length > 0"
				:is-collapsed="isCollapsed"
				:has-experts="hasExperts"
				:are-all-settings-enabled="tab === 'all'"
				@save="save"
				@ready="isReady = true"
			/>
			<template v-if="isReady && configs !== undefined">
				<div class="flex gap-4 flex-wrap lg:flex-nowrap" v-if="tab !== 'all'">
					<div class="w-full lg:w-3xs shrink-0">
						<Menu :model="menu" :pt:root:class="'border-0 lg:sticky top-11 mt-2'"> </Menu>
					</div>
					<div class="w-full">
						<General :configs="configs" :hash="hash" v-if="tab === ''" @refresh="load" />
						<CssJs v-if="tab === 'CssJs'" />
						<template v-for="config in configs" :id="config.cat">
							<Fieldset
								v-if="tab === config.cat"
								:legend="config.name"
								class="border-b-0 border-r-0 rounded-r-none rounded-b-none pb-20"
							>
								<div
									class="configDescription w-full text-muted-color-emphasis pl-6 pb-8"
									v-if="config.description"
									v-html="config.description"
								></div>
								<ConfigGroup :configs="config.configs" @filled="update" @reset="reset" />
							</Fieldset>
						</template>
					</div>
				</div>
				<AllSettings v-else :configs="configs" :hash="hash" @filled="update" @reset="reset" />
			</template>
			<ScrollTop :threshold="50" />
		</div>
	</div>
</template>
<script setup lang="ts">
import Toolbar from "primevue/toolbar";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import ScrollTop from "primevue/scrolltop";
import Menu from "primevue/menu";
import { useToast } from "primevue/usetoast";
import { ref } from "vue";
import { computed } from "vue";
import SettingsService from "@/services/settings-service";
import { trans } from "laravel-vue-i18n";
import { onMounted } from "vue";
import { useSplitter } from "@/composables/album/splitter";
import ConfigGroup from "@/components/settings/ConfigGroup.vue";
import ConfirmSave from "@/components/settings/ConfirmSave.vue";
import General from "@/components/settings/General.vue";
import Fieldset from "primevue/fieldset";
import AllSettings from "@/components/settings/AllSettings.vue";
import { MenuItem } from "primevue/menuitem";
import CssJs from "@/components/settings/CssJs.vue";
import { useRoute, useRouter } from "vue-router";
import { watch } from "vue";

const props = defineProps<{
	tab: string | undefined;
}>();

const toast = useToast();
const tab = ref(props.tab);
const router = useRouter();
const route = useRoute();
const isReady = ref(false);
const configs = ref<App.Http.Resources.Models.ConfigCategoryResource[] | undefined>(undefined);
const hash = ref("");
const modified = ref<App.Http.Resources.Editable.EditableConfigResource[]>([]);

const { spliter } = useSplitter();

const isCollapsed = computed(() => {
	return tab.value === "" || tab.value === "CssJs";
});

const hasExperts = computed(() => {
	if (configs.value === undefined) {
		return false;
	}
	if (tab.value === "all") {
		return true;
	}

	return configs.value?.filter((c) => c.cat === tab.value).some((c) => c.configs.some((config) => config.is_expert));
});

const menu = computed(() => {
	if (configs.value === undefined) {
		return [];
	}

	const categories = spliter(
		configs.value,
		(c) => {
			if (c.priority < 10) {
				return "system";
			}
			if (c.priority < 100) {
				return "modules";
			}
			return "advanced";
		},
		(c) => {
			if (c.priority < 10) {
				return "settings.groups.system";
			}
			if (c.priority < 100) {
				return "settings.groups.modules";
			}
			return "settings.groups.advanced";
		},
	);

	const menu: MenuItem[] = [];
	menu.push({
		label: trans("settings.groups.general"),
		command: () => router.push({ name: "settings", params: { tab: "" } }),
	});
	categories.forEach((c) => {
		menu.push({
			separator: true,
		});
		menu.push({
			label: trans(c.header),
			items: c.data.map((item) => {
				return {
					label: item.name,
					command: () => router.push({ name: "settings", params: { tab: item.cat } }),
				};
			}),
		});
	});
	menu[menu.length - 1].items?.push(
		{
			label: trans("settings.cssjs.header"),
			command: () => router.push({ name: "settings", params: { tab: "CssJs" } }),
		},
		{
			icon: "pi pi-exclamation-triangle text-warning-600",
			label: trans("settings.tabs.all_settings"),
			command: () => router.push({ name: "settings", params: { tab: "all" } }),
		},
	);

	return menu;
});

function load() {
	SettingsService.getAll().then((response) => {
		configs.value = response.data as App.Http.Resources.Models.ConfigCategoryResource[];
		hash.value = Math.random().toString(16).substring(2, 12);
	});
}

function update(configKey: string, value: string) {
	const config = modified.value.find((c) => c.key === configKey);
	if (config) {
		config.value = value;
	} else {
		const configData = {
			key: configKey,
			value: value,
		};
		modified.value.push(configData);
	}
}

function reset(configKey: string) {
	const index = modified.value.findIndex((c) => c.key === configKey);
	if (index !== -1) {
		modified.value.splice(index, 1);
	}
}

function save() {
	SettingsService.setConfigs({ configs: modified.value })
		.then(() => {
			setDarkMode(); // This is a hack to set the dark mode immediately
			modified.value = [];
			toast.add({ severity: "success", summary: trans("settings.toasts.change_saved"), detail: trans("settings.toasts.details"), life: 3000 });
			load();
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("settings.toasts.error"), detail: e.response.data.message, life: 3000 });
		});
}

function setDarkMode() {
	const isDark = modified.value.find((c) => c.key === "dark_mode_enabled")?.value;
	if (isDark === "1") {
		document.body.classList.add("dark");
	} else if (isDark === "0") {
		document.body.classList.remove("dark");
	}
}

onMounted(() => {
	load();
});

watch(
	() => route.params.tab,
	(newTab, _oldTab) => {
		tab.value = newTab as string | undefined;
	},
);
</script>
<style lang="css">
/* Kill the border of ScrollTop */
.p-scrolltop {
	border: none;
}
</style>
