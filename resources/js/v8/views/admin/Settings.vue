<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("settings.title") }}
	</UHeader>
	<div class="text-center lg:hidden font-bold text-error py-3" v-html="$t('settings.small_screen')"></div>
	<div class="pl-[calc(100vw-100%)] flex justify-center pb-10">
		<div class="max-w-6xl w-full p-4">
			<ConfirmSave
				:is-save-visible="modified.length > 0"
				:is-collapsed="isCollapsed"
				:has-experts="hasExperts"
				:are-all-settings-enabled="tab === 'all'"
				@save="save"
				@ready="isReady = true"
			/>
			<template v-if="isReady && configs !== undefined">
				<div v-if="tab !== 'all'" class="flex gap-4 flex-wrap lg:flex-nowrap">
					<div class="w-full lg:w-3xs shrink-0">
						<nav class="border-0 lg:sticky top-11 mt-2 flex flex-col">
							<a
								class="px-4 py-2 rounded hover:bg-elevated cursor-pointer"
								@click="router.push({ name: 'settings', params: { tab: '' } })"
							>
								{{ $t("settings.groups.general") }}
							</a>
							<template v-for="(group, gIdx) in menuGroups" :key="`group-${gIdx}`">
								<hr class="my-2 border-default" />
								<span class="px-4 py-1 text-xs uppercase text-muted font-semibold">{{ $t(group.header) }}</span>
								<a
									v-for="item in group.items"
									:key="item.cat"
									class="px-4 py-2 rounded hover:bg-elevated cursor-pointer"
									@click="router.push({ name: 'settings', params: { tab: item.cat } })"
								>
									{{ item.label }}
								</a>
							</template>
							<hr class="my-2 border-default" />
							<a
								class="px-4 py-2 rounded hover:bg-elevated cursor-pointer"
								@click="router.push({ name: 'settings', params: { tab: 'CssJs' } })"
							>
								{{ $t("settings.cssjs.header") }}
							</a>
							<a
								class="px-4 py-2 rounded hover:bg-elevated cursor-pointer flex items-center gap-2"
								@click="router.push({ name: 'settings', params: { tab: 'all' } })"
							>
								<UIcon name="prime:exclamation-triangle" class="text-warning-600" />
								{{ $t("settings.tabs.all_settings") }}
							</a>
						</nav>
					</div>
					<div class="w-full">
						<General v-if="tab === ''" :configs="configs" :hash="hash" @refresh="load" />
						<CssJs v-if="tab === 'CssJs'" />
						<template v-for="config in configs" :key="config.cat">
							<Fieldset v-if="tab === config.cat" :legend="tCatName({ key: config.cat, name: config.name })" class="pb-20 h-full">
								<div
									v-if="tCatDesc({ key: config.cat, description: config.description })"
									class="configDescription w-full text-highlighted pl-6 pb-8"
									v-html="tCatDesc({ key: config.cat, description: config.description })"
								></div>
								<ConfigGroup :configs="config.configs" @filled="update" @reset="reset" />
							</Fieldset>
						</template>
					</div>
				</div>
				<AllSettings v-else :configs="configs" :hash="hash" @filled="update" @reset="reset" />
			</template>
		</div>
	</div>
</template>
<script setup lang="ts">
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { ref } from "vue";
import { computed } from "vue";
import SettingsService from "@/services/settings-service";
import { trans } from "laravel-vue-i18n";
import { onMounted } from "vue";
import { useSplitter } from "@/composables/album/splitter";
import ConfigGroup from "@/v8/components/settings/ConfigGroup.vue";
import ConfirmSave from "@/v8/components/settings/ConfirmSave.vue";
import General from "@/v8/components/settings/General.vue";
import AllSettings from "@/v8/components/settings/AllSettings.vue";
import CssJs from "@/v8/components/settings/CssJs.vue";
import { useRoute, useRouter } from "vue-router";
import { watch } from "vue";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
import { useTranslation } from "@/composables/useTranslation";

const props = defineProps<{
	tab: string | undefined;
}>();

const toast = useAppToast();
const tab = ref(props.tab ?? ""); // Default to empty string if props.tab is undefined
const router = useRouter();
const route = useRoute();
const isReady = ref(false);
const configs = ref<App.Http.Resources.Models.ConfigCategoryResource[] | undefined>(undefined);
const hash = ref("");
const modified = ref<App.Http.Resources.Editable.EditableConfigResource[]>([]);

const { spliter } = useSplitter();
const { tCatName, tCatDesc } = useTranslation();

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

const menuGroups = computed(() => {
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

	return categories.map((c) => ({
		header: c.header,
		items: c.data.map((item) => ({
			cat: item.cat,
			label: tCatName({ key: item.cat, name: item.name }),
		})),
	}));
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
		tab.value = (newTab as string | undefined) ?? "";
	},
);
</script>
