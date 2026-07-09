<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("admin-dashboard.nsfw_config.title") }}
	</UHeader>

	<div class="max-w-5xl mx-auto mt-4 px-4 pb-8">
		<!-- Loading -->
		<div v-if="settingsLoading && configs.length === 0" class="flex justify-center py-12">
			<Spinner class="text-3xl" />
		</div>

		<template v-else>
			<UTabs v-model="activeTab" :items="tabItems" class="w-full">
				<template #settings>
					<p class="text-muted mb-6 text-center text-sm">{{ $t("admin-dashboard.nsfw_config.description") }}</p>

					<div class="flex flex-col gap-6">
						<!-- Enable + Preset + Scan trusted -->
						<Fieldset :legend="$t('admin-dashboard.nsfw_config.section_general')">
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

						<!-- Trust-tier x finding matrix (read-only) -->
						<Fieldset :legend="$t('admin-dashboard.nsfw_config.section_matrix')">
							<p class="text-muted text-sm mb-4" v-html="$t('admin-dashboard.nsfw_config.matrix_explanation')" />
							<div class="overflow-x-auto">
								<table class="w-full text-sm border-collapse">
									<thead>
										<tr class="text-left text-muted border-b border-muted">
											<th class="py-2 pr-4">{{ $t("admin-dashboard.nsfw_config.matrix_trust_level") }}</th>
											<th class="py-2 pr-4">{{ $t("admin-dashboard.nsfw_config.block") }}</th>
											<th class="py-2 pr-4">{{ $t("admin-dashboard.nsfw_config.review") }}</th>
											<th class="py-2">{{ $t("admin-dashboard.nsfw_config.sensitive") }}</th>
										</tr>
									</thead>
									<tbody>
										<tr
											v-for="(row, idx) in matrixRows"
											:key="idx"
											:class="idx < matrixRows.length - 1 ? 'border-b border-default' : ''"
										>
											<td class="py-2 pr-4 font-medium flex items-center gap-2">
												<UIcon :name="row.iconClass" />{{ row.trustLevel }}
											</td>
											<td class="py-2 pr-4">
												<UBadge :color="row.block.severity" class="text-xs">{{ row.block.label }}</UBadge>
											</td>
											<td class="py-2 pr-4">
												<UBadge :color="row.review.severity" class="text-xs">{{ row.review.label }}</UBadge>
											</td>
											<td class="py-2">
												<UBadge :color="row.sensitive.severity" class="text-xs">{{ row.sensitive.label }}</UBadge>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="flex flex-wrap gap-x-6 gap-y-2 mt-4 text-xs text-muted">
								<span class="inline-flex items-center gap-1"
									><UBadge color="error" class="text-xs">{{ $t("admin-dashboard.nsfw_config.block") }}</UBadge
									>{{ $t("admin-dashboard.nsfw_config.legend_block") }}</span
								>
								<span class="inline-flex items-center gap-1"
									><UBadge color="warning" class="text-xs">{{ $t("admin-dashboard.nsfw_config.matrix_moderate") }}</UBadge
									>{{ $t("admin-dashboard.nsfw_config.legend_moderate") }}</span
								>
								<span class="inline-flex items-center gap-1"
									><UBadge color="success" class="text-xs">{{ $t("admin-dashboard.nsfw_config.matrix_approve") }}</UBadge
									>{{ $t("admin-dashboard.nsfw_config.legend_approve") }}</span
								>
								<span class="inline-flex items-center gap-1"
									><UBadge color="info" class="text-xs">{{ $t("admin-dashboard.nsfw_config.matrix_mark_album") }}</UBadge
									>{{ $t("admin-dashboard.nsfw_config.legend_mark_album") }}</span
								>
								<span class="inline-flex items-center gap-1"
									><UBadge color="neutral" class="text-xs">{{ $t("admin-dashboard.nsfw_config.matrix_nothing") }}</UBadge
									>{{ $t("admin-dashboard.nsfw_config.legend_nothing") }}</span
								>
							</div>
						</Fieldset>
						<div class="flex gap-8 flex-wrap">
							<!-- Block finding actions -->
							<Fieldset :legend="$t('admin-dashboard.nsfw_config.section_actions')">
								<p class="text-muted text-sm mb-4" v-html="$t('admin-dashboard.nsfw_config.actions_explanation')" />
								<div class="flex flex-col gap-3">
									<div v-for="row in blockActionRows" :key="row.key" class="flex items-center justify-between gap-4">
										<div class="flex items-center gap-2 min-w-0">
											<UIcon :name="row.iconClass" />
											<span class="font-medium">{{ row.label }}</span>
										</div>
										<USelectMenu
											v-if="cfg[row.key]"
											:model-value="cfg[row.key]!.value"
											:items="cfg[row.key]!.type.split('|')"
											class="shrink-0"
											@update:model-value="(v: string | number) => save(row.key, v as string)"
										/>
									</div>
								</div>
								<p class="text-muted text-sm mt-6 mb-4" v-html="$t('admin-dashboard.nsfw_config.sensitive_explanation')" />
								<div class="flex flex-col gap-3">
									<div v-for="row in sensitiveActionRows" :key="row.key" class="flex items-center justify-between gap-4">
										<span class="text-highlighted">{{ row.label }}</span>
										<USelectMenu
											v-if="cfg[row.key]"
											:model-value="cfg[row.key]!.value"
											:items="cfg[row.key]!.type.split('|')"
											class="shrink-0"
											@update:model-value="(v: string | number) => save(row.key, v as string)"
										/>
									</div>
								</div>
							</Fieldset>

							<!-- Hide on scan -->
							<Fieldset :legend="$t('admin-dashboard.nsfw_config.section_hide_on_scan')">
								<p class="text-muted text-sm mb-2" v-html="$t('admin-dashboard.nsfw_config.hide_on_scan_explanation')" />
								<p class="text-sm my-4 text-highlighted flex items-start gap-2">
									<UIcon name="prime:exclamation-triangle" class="text-amber-600" />
									<span v-html="$t('admin-dashboard.nsfw_config.hide_on_scan_warning')" />
								</p>
								<div class="flex flex-col gap-3">
									<div v-for="row in hideOnScanRows" :key="row.key" class="flex items-center justify-between gap-4">
										<div class="flex items-center gap-2">
											<UIcon :name="row.iconClass" />
											<span class="text-highlighted">{{ row.label }}</span>
										</div>
										<USwitch
											v-if="cfg[row.key]"
											:model-value="cfg[row.key]!.value === '1'"
											@update:model-value="(v: boolean) => save(row.key, v ? '1' : '0')"
										/>
									</div>
								</div>
							</Fieldset>
						</div>
					</div>
				</template>

				<template #presets>
					<div class="flex justify-end mb-4">
						<UButton icon="prime:refresh" variant="ghost" color="neutral" :loading="presetsLoading" @click="fetchPresets" />
					</div>

					<!-- Loading -->
					<div v-if="presetsLoading && !presetsData" class="flex justify-center py-12">
						<Spinner class="text-3xl" />
					</div>

					<!-- Error -->
					<UCard v-else-if="presetsError">
						<div class="text-center py-8">
							<UIcon name="prime:exclamation-triangle" class="text-4xl text-orange-500 mb-4" />
							<p class="text-muted">{{ presetsError }}</p>
						</div>
					</UCard>

					<!-- Content -->
					<template v-else-if="presetsData">
						<!-- Service Runtime Config -->
						<UCard class="mb-4">
							<template #header>{{ $t("admin-dashboard.nsfw_config.runtime_config") }}</template>
							<UTable :data="runtimeConfigRows" :columns="runtimeConfigColumns" class="text-sm" />
						</UCard>

						<!-- Available Presets -->
						<UCard>
							<template #header
								>{{ $t("admin-dashboard.nsfw_config.presets") }} ({{ Object.keys(presetsData.presets).length }})</template
							>
							<div class="flex flex-col gap-4">
								<UCard v-for="(preset, name) in presetsData.presets" :key="name">
									<template #header>
										<div class="flex items-center gap-2">
											<span class="font-bold">{{ preset.name }}</span>
											<UBadge v-if="isActivePreset(String(name))" color="success">{{
												$t("admin-dashboard.nsfw_config.active")
											}}</UBadge>
										</div>
									</template>
									<p class="text-muted text-sm mb-3">{{ preset.description }}</p>

									<div class="flex flex-col gap-2">
										<div>
											<span class="font-semibold text-sm">{{ $t("admin-dashboard.nsfw_config.block") }}:</span>
											<div class="flex flex-wrap gap-1 mt-1">
												<UBadge v-for="label in preset.block.labels" :key="label" color="error" class="text-xs">{{
													label
												}}</UBadge>
												<span v-if="preset.block.labels.length === 0" class="text-muted text-xs italic">{{
													$t("admin-dashboard.nsfw_config.none")
												}}</span>
											</div>
										</div>
										<div>
											<span class="font-semibold text-sm">{{ $t("admin-dashboard.nsfw_config.review") }}:</span>
											<div class="flex flex-wrap gap-1 mt-1">
												<UBadge v-for="label in preset.review.labels" :key="label" color="warning" class="text-xs">{{
													label
												}}</UBadge>
												<span v-if="preset.review.labels.length === 0" class="text-muted text-xs italic">{{
													$t("admin-dashboard.nsfw_config.none")
												}}</span>
											</div>
										</div>
										<div>
											<span class="font-semibold text-sm">{{ $t("admin-dashboard.nsfw_config.sensitive") }}:</span>
											<div class="flex flex-wrap gap-1 mt-1">
												<UBadge v-for="label in preset.sensitive.labels" :key="label" color="info" class="text-xs">{{
													label
												}}</UBadge>
												<span v-if="preset.sensitive.labels.length === 0" class="text-muted text-xs italic">{{
													$t("admin-dashboard.nsfw_config.none")
												}}</span>
											</div>
										</div>
									</div>
								</UCard>
							</div>
						</UCard>
					</template>
				</template>
			</UTabs>
		</template>
	</div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import BoolField from "@/v8/components/forms/settings/BoolField.vue";
import SelectField from "@/v8/components/forms/settings/SelectField.vue";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
import Spinner from "@/v8/components/Spinner.vue";
import NsfwConfigService from "@/services/nsfw-config-service";
import SettingsService from "@/services/settings-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import type { TableColumn, TabsItem } from "@nuxt/ui";

type CfgRef = App.Http.Resources.Models.ConfigResource | undefined;

const toast = useAppToast();
const activeTab = ref("settings");

const tabItems: TabsItem[] = [
	{ label: trans("admin-dashboard.nsfw_config.tab_settings"), value: "settings", icon: "prime:cog", slot: "settings" },
	{ label: trans("admin-dashboard.nsfw_config.tab_presets"), value: "presets", icon: "prime:eye", slot: "presets" },
];

// -- Settings tab state --
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
				const found = all.find((c) => c.key === key);
				if (found) {
					found.require_se = false;
				}
				cfg[key] = found;
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

// -- Action & hide-on-scan rows --
type NsfwKey = (typeof NSFW_KEYS)[number];
type ActionRow = { key: NsfwKey; iconClass: string; label: string };

const blockActionRows: ActionRow[] = [
	{
		key: "ai_vision_nsfw_check_block_action",
		iconClass: "prime:shield text-error",
		label: trans("admin-dashboard.nsfw_config.matrix_check"),
	},
	{
		key: "ai_vision_nsfw_monitor_block_action",
		iconClass: "prime:shield text-yellow-500",
		label: trans("admin-dashboard.nsfw_config.matrix_monitor"),
	},
	{
		key: "ai_vision_nsfw_trust_but_verify_block_action",
		iconClass: "prime:shield text-blue-500",
		label: trans("admin-dashboard.nsfw_config.matrix_tbv"),
	},
	{
		key: "ai_vision_nsfw_trust_block_action",
		iconClass: "prime:shield text-success",
		label: trans("admin-dashboard.nsfw_config.matrix_trusted"),
	},
];

const sensitiveActionRows: ActionRow[] = [
	{ key: "ai_vision_nsfw_sensitive_album_action", iconClass: "", label: trans("admin-dashboard.nsfw_config.sensitive_album") },
	{ key: "ai_vision_nsfw_sensitive_no_album_action", iconClass: "", label: trans("admin-dashboard.nsfw_config.sensitive_no_album") },
];

const hideOnScanRows: ActionRow[] = [
	{
		key: "ai_vision_nsfw_monitor_hide_on_scan",
		iconClass: "prime:shield text-yellow-500",
		label: trans("admin-dashboard.nsfw_config.matrix_monitor"),
	},
	{
		key: "ai_vision_nsfw_trust_but_verify_hide_on_scan",
		iconClass: "prime:shield text-blue-500",
		label: trans("admin-dashboard.nsfw_config.matrix_tbv"),
	},
	{
		key: "ai_vision_nsfw_trust_hide_on_scan",
		iconClass: "prime:shield text-success",
		label: trans("admin-dashboard.nsfw_config.matrix_trusted"),
	},
];

// -- Matrix (derived from current config) --
type MatrixCell = { label: string; severity: "error" | "warning" | "success" | "info" | "neutral" };

function blockActionCell(configRef: CfgRef): MatrixCell {
	switch (configRef?.value) {
		case "block":
			return { label: trans("admin-dashboard.nsfw_config.block"), severity: "error" };
		case "moderate":
			return { label: trans("admin-dashboard.nsfw_config.matrix_moderate"), severity: "warning" };
		case "approve":
			return { label: trans("admin-dashboard.nsfw_config.matrix_approve"), severity: "success" };
		default:
			return { label: "—", severity: "neutral" };
	}
}

function sensitiveCell(): MatrixCell {
	switch (cfg.ai_vision_nsfw_sensitive_album_action?.value) {
		case "mark_album":
			return { label: trans("admin-dashboard.nsfw_config.matrix_mark_album"), severity: "info" };
		case "nothing":
			return { label: trans("admin-dashboard.nsfw_config.matrix_nothing"), severity: "neutral" };
		default:
			return { label: "—", severity: "neutral" };
	}
}

const matrixRows = computed(() => [
	{
		trustLevel: trans("admin-dashboard.nsfw_config.matrix_check"),
		iconClass: "prime:shield text-error",
		block: blockActionCell(cfg.ai_vision_nsfw_check_block_action),
		review: { label: trans("admin-dashboard.nsfw_config.matrix_moderate"), severity: "warning" as const },
		sensitive: { label: trans("admin-dashboard.nsfw_config.matrix_moderate"), severity: "warning" as const },
	},
	{
		trustLevel: trans("admin-dashboard.nsfw_config.matrix_monitor"),
		iconClass: "prime:shield text-yellow-500",
		block: blockActionCell(cfg.ai_vision_nsfw_monitor_block_action),
		review: { label: trans("admin-dashboard.nsfw_config.matrix_moderate"), severity: "warning" as const },
		sensitive: sensitiveCell(),
	},
	{
		trustLevel: trans("admin-dashboard.nsfw_config.matrix_tbv"),
		iconClass: "prime:shield text-blue-500",
		block: blockActionCell(cfg.ai_vision_nsfw_trust_but_verify_block_action),
		review: { label: trans("admin-dashboard.nsfw_config.matrix_approve"), severity: "success" as const },
		sensitive: sensitiveCell(),
	},
	{
		trustLevel: trans("admin-dashboard.nsfw_config.matrix_trusted"),
		iconClass: "prime:shield text-success",
		block: blockActionCell(cfg.ai_vision_nsfw_trust_block_action),
		review: { label: trans("admin-dashboard.nsfw_config.matrix_approve"), severity: "success" as const },
		sensitive: sensitiveCell(),
	},
]);

// -- Presets tab state --
const presetsLoading = ref(false);
const presetsError = ref<string | null>(null);
const presetsData = ref<App.Http.Resources.GalleryConfigs.Nsfw.NsfwConfigResource | null>(null);

const jsonKeys = new Set(["block", "review", "sensitive"]);

type RuntimeConfigRow = { key: string; value: unknown };

const runtimeConfigRows = computed<RuntimeConfigRow[]>(() => {
	if (!presetsData.value?.config) return [];
	return Object.entries(presetsData.value.config)
		.filter(([key]) => !jsonKeys.has(key))
		.map(([key, value]) => ({ key, value }));
});

const runtimeConfigColumns: TableColumn<RuntimeConfigRow>[] = [
	{ accessorKey: "key", header: trans("admin-dashboard.nsfw_config.key") },
	{ accessorKey: "value", header: trans("admin-dashboard.nsfw_config.value") },
];

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

// -- Init --
onMounted(() => {
	loadSettings();
	fetchPresets();
});
</script>
