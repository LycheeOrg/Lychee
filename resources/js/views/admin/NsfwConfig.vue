<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>
		<template #center>
			{{ $t("admin-dashboard.nsfw_config.title") }}
		</template>
	</Toolbar>

	<div class="max-w-5xl mx-auto mt-4 px-4 pb-8">
		<!-- Loading -->
		<div v-if="settingsLoading && configs.length === 0" class="flex justify-center py-12">
			<ProgressSpinner />
		</div>

		<template v-else>
			<Tabs v-model:value="activeTab" class="w-full">
				<TabList class="mx-0 border-b border-surface-200 dark:border-surface-700">
					<Tab value="settings" class="px-4 py-2"> <i class="pi pi-cog mr-2"></i>{{ $t("admin-dashboard.nsfw_config.tab_settings") }} </Tab>
					<Tab value="presets" class="px-4 py-2"> <i class="pi pi-eye mr-2"></i>{{ $t("admin-dashboard.nsfw_config.tab_presets") }} </Tab>
				</TabList>
				<TabPanels class="p-0 pt-4">
					<!-- ═══════════════════════════════════════════ -->
					<!-- TAB 1: Settings                            -->
					<!-- ═══════════════════════════════════════════ -->
					<TabPanel value="settings">
						<p class="text-muted-color mb-6 text-center text-sm">{{ $t("admin-dashboard.nsfw_config.description") }}</p>

						<div class="flex flex-col gap-6">
							<!-- Enable + Preset + Scan trusted -->
							<Fieldset>
								<div class="flex flex-col gap-4">
									<BoolField
										v-if="cfg.ai_vision_nsfw_enabled"
										:label="$t('admin-dashboard.nsfw_config.enable')"
										:config="cfg.ai_vision_nsfw_enabled"
										@filled="save"
									/>
									<SelectField
										v-if="cfg.ai_vision_nsfw_preset"
										:label="$t('admin-dashboard.nsfw_config.preset')"
										:config="cfg.ai_vision_nsfw_preset"
										@filled="save"
									/>
									<BoolField
										v-if="cfg.ai_vision_nsfw_scan_trusted_users"
										:label="$t('admin-dashboard.nsfw_config.scan_trusted')"
										:config="cfg.ai_vision_nsfw_scan_trusted_users"
										@filled="save"
									/>
								</div>
							</Fieldset>

							<!-- Block finding actions -->
							<Fieldset :legend="$t('admin-dashboard.nsfw_config.section_actions')">
								<div class="flex flex-col gap-4">
									<SelectField
										v-if="cfg.ai_vision_nsfw_check_block_action"
										:label="$t('admin-dashboard.nsfw_config.block_check')"
										:config="cfg.ai_vision_nsfw_check_block_action"
										@filled="save"
									/>
									<SelectField
										v-if="cfg.ai_vision_nsfw_monitor_block_action"
										:label="$t('admin-dashboard.nsfw_config.block_monitor')"
										:config="cfg.ai_vision_nsfw_monitor_block_action"
										@filled="save"
									/>
									<SelectField
										v-if="cfg.ai_vision_nsfw_trust_but_verify_block_action"
										:label="$t('admin-dashboard.nsfw_config.block_tbv')"
										:config="cfg.ai_vision_nsfw_trust_but_verify_block_action"
										@filled="save"
									/>
									<SelectField
										v-if="cfg.ai_vision_nsfw_trust_block_action"
										:label="$t('admin-dashboard.nsfw_config.block_trusted')"
										:config="cfg.ai_vision_nsfw_trust_block_action"
										@filled="save"
									/>
									<div class="my-2" />
									<SelectField
										v-if="cfg.ai_vision_nsfw_sensitive_album_action"
										:label="$t('admin-dashboard.nsfw_config.sensitive_album')"
										:config="cfg.ai_vision_nsfw_sensitive_album_action"
										@filled="save"
									/>
									<SelectField
										v-if="cfg.ai_vision_nsfw_sensitive_no_album_action"
										:label="$t('admin-dashboard.nsfw_config.sensitive_no_album')"
										:config="cfg.ai_vision_nsfw_sensitive_no_album_action"
										@filled="save"
									/>
								</div>
							</Fieldset>

							<!-- Hide on scan -->
							<Fieldset :legend="$t('admin-dashboard.nsfw_config.section_hide_on_scan')">
								<div class="flex flex-col gap-4">
									<BoolField
										v-if="cfg.ai_vision_nsfw_monitor_hide_on_scan"
										:label="$t('admin-dashboard.nsfw_config.hide_monitor')"
										:config="cfg.ai_vision_nsfw_monitor_hide_on_scan"
										@filled="save"
									/>
									<BoolField
										v-if="cfg.ai_vision_nsfw_trust_but_verify_hide_on_scan"
										:label="$t('admin-dashboard.nsfw_config.hide_tbv')"
										:config="cfg.ai_vision_nsfw_trust_but_verify_hide_on_scan"
										@filled="save"
									/>
									<BoolField
										v-if="cfg.ai_vision_nsfw_trust_hide_on_scan"
										:label="$t('admin-dashboard.nsfw_config.hide_trusted')"
										:config="cfg.ai_vision_nsfw_trust_hide_on_scan"
										@filled="save"
									/>
									<Message severity="warn" :closable="false" class="text-sm">
										<i class="pi pi-exclamation-triangle mr-2" />{{ $t("admin-dashboard.nsfw_config.hide_on_scan_warning") }}
									</Message>
								</div>
							</Fieldset>

							<!-- Trust-tier × finding matrix (read-only) -->
							<Fieldset :legend="$t('admin-dashboard.nsfw_config.section_matrix')">
								<div class="overflow-x-auto">
									<table class="w-full text-sm border-collapse">
										<thead>
											<tr class="text-left text-muted-color border-b border-surface-200 dark:border-surface-700">
												<th class="py-2 pr-4">{{ $t("admin-dashboard.nsfw_config.matrix_trust_level") }}</th>
												<th class="py-2 pr-4">{{ $t("admin-dashboard.nsfw_config.block") }}</th>
												<th class="py-2 pr-4">{{ $t("admin-dashboard.nsfw_config.review") }}</th>
												<th class="py-2">{{ $t("admin-dashboard.nsfw_config.sensitive") }}</th>
											</tr>
										</thead>
										<tbody>
											<tr class="border-b border-surface-100 dark:border-surface-800">
												<td class="py-2 pr-4 font-medium">{{ $t("admin-dashboard.nsfw_config.matrix_check") }}</td>
												<td class="py-2 pr-4">
													<Tag
														severity="danger"
														:value="$t('admin-dashboard.nsfw_config.matrix_block_moderate')"
														class="text-xs"
													/>
												</td>
												<td class="py-2 pr-4">
													<Tag severity="warn" :value="$t('admin-dashboard.nsfw_config.matrix_moderate')" class="text-xs" />
												</td>
												<td class="py-2">
													<Tag
														severity="info"
														:value="$t('admin-dashboard.nsfw_config.matrix_moderate_album')"
														class="text-xs"
													/>
												</td>
											</tr>
											<tr class="border-b border-surface-100 dark:border-surface-800">
												<td class="py-2 pr-4 font-medium">{{ $t("admin-dashboard.nsfw_config.matrix_monitor") }}</td>
												<td class="py-2 pr-4">
													<Tag
														severity="danger"
														:value="$t('admin-dashboard.nsfw_config.matrix_block_moderate')"
														class="text-xs"
													/>
												</td>
												<td class="py-2 pr-4">
													<Tag severity="warn" :value="$t('admin-dashboard.nsfw_config.matrix_moderate')" class="text-xs" />
												</td>
												<td class="py-2">
													<Tag
														severity="info"
														:value="$t('admin-dashboard.nsfw_config.matrix_album_or_nothing')"
														class="text-xs"
													/>
												</td>
											</tr>
											<tr class="border-b border-surface-100 dark:border-surface-800">
												<td class="py-2 pr-4 font-medium">{{ $t("admin-dashboard.nsfw_config.matrix_tbv") }}</td>
												<td class="py-2 pr-4">
													<Tag
														severity="danger"
														:value="$t('admin-dashboard.nsfw_config.matrix_block_moderate')"
														class="text-xs"
													/>
												</td>
												<td class="py-2 pr-4">
													<Tag
														severity="success"
														:value="$t('admin-dashboard.nsfw_config.matrix_approve')"
														class="text-xs"
													/>
												</td>
												<td class="py-2">
													<Tag
														severity="info"
														:value="$t('admin-dashboard.nsfw_config.matrix_album_or_nothing')"
														class="text-xs"
													/>
												</td>
											</tr>
											<tr>
												<td class="py-2 pr-4 font-medium">{{ $t("admin-dashboard.nsfw_config.matrix_trusted") }}</td>
												<td class="py-2 pr-4">
													<Tag
														severity="danger"
														:value="$t('admin-dashboard.nsfw_config.matrix_block_moderate_approve')"
														class="text-xs"
													/>
												</td>
												<td class="py-2 pr-4">
													<Tag
														severity="success"
														:value="$t('admin-dashboard.nsfw_config.matrix_approve')"
														class="text-xs"
													/>
												</td>
												<td class="py-2">
													<Tag
														severity="info"
														:value="$t('admin-dashboard.nsfw_config.matrix_album_or_nothing')"
														class="text-xs"
													/>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</Fieldset>
						</div>
					</TabPanel>

					<!-- ═══════════════════════════════════════════ -->
					<!-- TAB 2: Presets                             -->
					<!-- ═══════════════════════════════════════════ -->
					<TabPanel value="presets">
						<div class="flex justify-end mb-4">
							<Button icon="pi pi-refresh" text rounded class="border-none" :loading="presetsLoading" @click="fetchPresets" />
						</div>

						<!-- Loading -->
						<div v-if="presetsLoading && !presetsData" class="flex justify-center py-12">
							<ProgressSpinner />
						</div>

						<!-- Error -->
						<Panel v-else-if="presetsError" class="border-0">
							<div class="text-center py-8">
								<i class="pi pi-exclamation-triangle text-4xl text-orange-500 mb-4"></i>
								<p class="text-muted-color">{{ presetsError }}</p>
							</div>
						</Panel>

						<!-- Content -->
						<template v-else-if="presetsData">
							<!-- Service Runtime Config -->
							<Panel :header="$t('admin-dashboard.nsfw_config.runtime_config')" class="border-0 mb-4">
								<DataTable :value="runtimeConfigRows" class="text-sm">
									<Column field="key" :header="$t('admin-dashboard.nsfw_config.key')" />
									<Column field="value" :header="$t('admin-dashboard.nsfw_config.value')" />
								</DataTable>
							</Panel>

							<!-- Available Presets -->
							<Panel
								:header="$t('admin-dashboard.nsfw_config.presets') + ` (${Object.keys(presetsData.presets).length})`"
								class="border-0"
							>
								<div class="flex flex-col gap-4">
									<Panel
										v-for="(preset, name) in presetsData.presets"
										:key="name"
										class="border-surface-200 dark:border-surface-700"
									>
										<template #header>
											<div class="flex items-center gap-2">
												<span class="font-bold">{{ preset.name }}</span>
												<Tag
													v-if="isActivePreset(String(name))"
													severity="success"
													:value="$t('admin-dashboard.nsfw_config.active')"
												/>
											</div>
										</template>
										<p class="text-muted-color text-sm mb-3">{{ preset.description }}</p>

										<div class="flex flex-col gap-2">
											<div>
												<span class="font-semibold text-sm">{{ $t("admin-dashboard.nsfw_config.block") }}:</span>
												<div class="flex flex-wrap gap-1 mt-1">
													<Tag
														v-for="label in preset.block.labels"
														:key="label"
														severity="danger"
														:value="label"
														class="text-xs"
													/>
													<span v-if="preset.block.labels.length === 0" class="text-muted-color text-xs italic">{{
														$t("admin-dashboard.nsfw_config.none")
													}}</span>
												</div>
											</div>
											<div>
												<span class="font-semibold text-sm">{{ $t("admin-dashboard.nsfw_config.review") }}:</span>
												<div class="flex flex-wrap gap-1 mt-1">
													<Tag
														v-for="label in preset.review.labels"
														:key="label"
														severity="warn"
														:value="label"
														class="text-xs"
													/>
													<span v-if="preset.review.labels.length === 0" class="text-muted-color text-xs italic">{{
														$t("admin-dashboard.nsfw_config.none")
													}}</span>
												</div>
											</div>
											<div>
												<span class="font-semibold text-sm">{{ $t("admin-dashboard.nsfw_config.sensitive") }}:</span>
												<div class="flex flex-wrap gap-1 mt-1">
													<Tag
														v-for="label in preset.sensitive.labels"
														:key="label"
														severity="info"
														:value="label"
														class="text-xs"
													/>
													<span v-if="preset.sensitive.labels.length === 0" class="text-muted-color text-xs italic">{{
														$t("admin-dashboard.nsfw_config.none")
													}}</span>
												</div>
											</div>
										</div>
									</Panel>
								</div>
							</Panel>
						</template>
					</TabPanel>
				</TabPanels>
			</Tabs>
		</template>
	</div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from "vue";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Message from "primevue/message";
import Panel from "primevue/panel";
import ProgressSpinner from "primevue/progressspinner";
import Tab from "primevue/tab";
import TabList from "primevue/tablist";
import TabPanel from "primevue/tabpanel";
import TabPanels from "primevue/tabpanels";
import Tabs from "primevue/tabs";
import Tag from "primevue/tag";
import Toolbar from "primevue/toolbar";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import BoolField from "@/components/forms/settings/BoolField.vue";
import SelectField from "@/components/forms/settings/SelectField.vue";
import Fieldset from "@/components/forms/basic/Fieldset.vue";
import NsfwConfigService, { type NsfwConfigResponse } from "@/services/nsfw-config-service";
import SettingsService from "@/services/settings-service";

type CfgRef = App.Http.Resources.Models.ConfigResource | undefined;

const toast = useToast();
const activeTab = ref("settings");

// ── Settings tab state ──────────────────────────────────────────
const settingsLoading = ref(false);
const configs = ref<App.Http.Resources.Models.ConfigCategoryResource[]>([]);

const NSFW_KEYS = [
	"ai_vision_nsfw_enabled",
	"ai_vision_nsfw_preset",
	"ai_vision_nsfw_check_block_action",
	"ai_vision_nsfw_monitor_block_action",
	"ai_vision_nsfw_trust_but_verify_block_action",
	"ai_vision_nsfw_trust_block_action",
	"ai_vision_nsfw_sensitive_album_action",
	"ai_vision_nsfw_sensitive_no_album_action",
	"ai_vision_nsfw_scan_trusted_users",
	"ai_vision_nsfw_monitor_hide_on_scan",
	"ai_vision_nsfw_trust_but_verify_hide_on_scan",
	"ai_vision_nsfw_trust_hide_on_scan",
] as const;

const cfg = reactive<Record<(typeof NSFW_KEYS)[number], CfgRef>>(
	Object.fromEntries(NSFW_KEYS.map((k) => [k, undefined])) as Record<(typeof NSFW_KEYS)[number], CfgRef>,
);

function loadSettings() {
	settingsLoading.value = true;
	SettingsService.getAll()
		.then((response) => {
			configs.value = response.data;
			const all: App.Http.Resources.Models.ConfigResource[] = [];
			for (const cat of response.data) {
				for (const c of cat.configs) {
					all.push(c);
				}
			}
			for (const key of NSFW_KEYS) {
				cfg[key] = all.find((c) => c.key === key);
			}
		})
		.catch(() => {})
		.finally(() => {
			settingsLoading.value = false;
		});
}

function save(configKey: string, value: string) {
	SettingsService.setConfigs({ configs: [{ key: configKey, value }] })
		.then(() => {
			toast.add({ severity: "success", summary: trans("settings.toasts.change_saved"), detail: trans("settings.toasts.details"), life: 3000 });
			loadSettings();
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: "Failed to save setting.", life: 3000 });
		});
}

// ── Presets tab state ───────────────────────────────────────────
const presetsLoading = ref(false);
const presetsError = ref<string | null>(null);
const presetsData = ref<NsfwConfigResponse | null>(null);

const jsonKeys = new Set(["block", "review", "sensitive"]);

const runtimeConfigRows = computed(() => {
	if (!presetsData.value?.config) return [];
	return Object.entries(presetsData.value.config)
		.filter(([key]) => !jsonKeys.has(key))
		.map(([key, value]) => ({ key, value }));
});

function isActivePreset(name: string): boolean {
	return name === (cfg.ai_vision_nsfw_preset?.value ?? "default");
}

function fetchPresets() {
	presetsLoading.value = true;
	presetsError.value = null;
	NsfwConfigService.getConfig()
		.then((response) => {
			presetsData.value = response.data;
		})
		.catch((e) => {
			presetsError.value = e.response?.data?.error ?? "Failed to load NSFW configuration.";
			presetsData.value = null;
		})
		.finally(() => {
			presetsLoading.value = false;
		});
}

// ── Init ────────────────────────────────────────────────────────
onMounted(() => {
	loadSettings();
	fetchPresets();
});
</script>
