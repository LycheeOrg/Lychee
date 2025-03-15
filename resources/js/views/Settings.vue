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
	<div class="pl-[calc(100vw-100%)] flex justify-center">
		<div class="max-w-6xl w-full">
			<ConfirmSave :is-visible="modified.length > 0" :is-general="is_collapsed" @save="save" @ready="isReady = true" />
			<template v-if="isReady && configs !== undefined">
				<div class="flex" v-if="!are_all_settings_enabled">
					<div class="w-3xs shrink-0">
						<Menu :model="menu" :pt:root:class="'border-0 sticky top-11'"> </Menu>
					</div>
					<div class="w-full">
						<EasySettings :configs="configs" :hash="hash" v-if="tab === ''" />
						<CssJs v-if="tab === 'CssJs'" />
						<template v-for="config in configs" :id="config.cat">
							<Fieldset
								v-if="tab === config.cat"
								:legend="config.name"
								class="border-b-0 border-r-0 rounded-r-none rounded-b-none pb-20"
							>
								<div class="configDescription w-full">
									{{ config.description }}
								</div>
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
import EasySettings from "@/components/settings/EasySettings.vue";
import Fieldset from "primevue/fieldset";
import AllSettings from "@/components/settings/AllSettings.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { MenuItem } from "primevue/menuitem";
import CssJs from "@/components/settings/CssJs.vue";

const toast = useToast();
const tab = ref("");
const isReady = ref(false);
const configs = ref<App.Http.Resources.Models.ConfigCategoryResource[] | undefined>(undefined);
const hash = ref("");
const modified = ref<App.Http.Resources.Editable.EditableConfigResource[]>([]);
const lycheeStore = useLycheeStateStore();
const { are_all_settings_enabled } = storeToRefs(lycheeStore);

const { spliter } = useSplitter();

const is_collapsed = computed(() => {
	return tab.value === "" && !are_all_settings_enabled.value;
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

	const categoryGroups: MenuItem[] = categories.map((m) => {
		return {
			label: trans(m.header),
			items: m.data.map((c) => {
				return {
					label: c.name,
					command: () => (tab.value = c.cat),
				};
			}),
		};
	});

	categoryGroups.unshift({
		label: trans("settings.groups.general"),
		command: () => (tab.value = ""),
	});

	categoryGroups[categoryGroups.length - 1].items?.push({
		label: trans("settings.cssjs.header"),
		command: () => (tab.value = "CssJs"),
	});

	return categoryGroups;
});

function load() {
	SettingsService.getAll().then((response) => {
		configs.value = response.data as App.Http.Resources.Models.ConfigCategoryResource[];
		hash.value = Math.random().toString(16).substring(2, 12);
		console.log(hash.value);
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
			modified.value = [];
			toast.add({ severity: "success", summary: trans("settings.toasts.change_saved"), detail: trans("settings.toasts.details"), life: 3000 });
			load();
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("settings.toasts.error"), detail: e.response.data.message, life: 3000 });
		});
}

onMounted(() => {
	load();
});
</script>
<style lang="css">
/* Kill the border of ScrollTop */
.p-scrolltop {
	border: none;
}
</style>
