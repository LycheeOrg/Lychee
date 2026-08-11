<template>
	<LandingLinkFormDialog v-model:open="isLinkFormVisible" :link="editingLink" :next-sort-order="links.length" @saved="loadLinks" />

	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("landing_config.title") }}
	</UHeader>

	<div class="max-w-7xl mx-auto mt-4 px-4 pb-8">
		<div v-if="loading" class="flex justify-center py-12">
			<LycheeLoadingIcon fast class="text-3xl" />
		</div>

		<template v-else>
			<UTabs v-model="activeTab" :items="tabItems" class="w-full" :dir="isLTR() ? 'ltr' : 'rtl'">
				<template #settings>
					<div class="flex flex-col xl:flex-row gap-6 mt-4">
						<!-- Settings form -->
						<div class="flex-1 flex flex-col gap-4">
							<Fieldset :legend="$t('landing_config.section_layout')">
								<div class="flex flex-col gap-4">
									<div class="flex items-center gap-4">
										<label class="font-semibold w-1/2">{{ $t("landing_config.field_layout") }}</label>
										<USelectMenu
											v-model="selectedLayout"
											:items="layoutOptions"
											label-key="label"
											:disabled-key="'disabled'"
											class="w-1/2"
										>
											<template #item-label="{ item }">
												{{ item.label }}
												<UBadge v-if="item.disabled" size="xs" color="primary" variant="subtle" class="ml-1">SE</UBadge>
											</template>
										</USelectMenu>
									</div>
									<USwitch
										v-model="draft.intro_screen_enabled"
										:label="$t('landing_config.field_intro_screen_enabled')"
										:ui="{ label: 'font-semibold' }"
									/>
								</div>
							</Fieldset>

							<Fieldset :legend="$t('landing_config.section_hero')">
								<div class="flex flex-col gap-4">
									<div class="flex items-center gap-4">
										<label class="font-semibold w-1/2">{{ $t("landing_config.field_hero_text_position") }}</label>
										<USelectMenu v-model="selectedPosition" :items="positionOptions" label-key="label" class="w-1/2">
											<template #item-label="{ item }">{{ item.label }}</template>
										</USelectMenu>
									</div>

									<ColorField
										:config="heroColorConfig"
										@filled="(_key, value) => (draft.hero_text_color = value)"
										@reset="() => (draft.hero_text_color = '')"
									/>

									<div class="flex items-center gap-4">
										<label class="font-semibold w-1/2">{{ $t("landing_config.field_hero_text_opacity") }}</label>
										<UInputNumber v-model="draft.hero_text_opacity" :min="0" :max="100" class="w-1/2" />
									</div>

									<div class="flex items-center gap-4">
										<label class="font-semibold w-1/2">{{ $t("landing_config.field_animation_preset") }}</label>
										<USelectMenu v-model="selectedAnimation" :items="animationOptions" label-key="label" class="w-1/2">
											<template #item-label="{ item }">
												{{ item.label }}
												<UBadge v-if="item.disabled" size="xs" color="primary" variant="subtle" class="ml-1">SE</UBadge>
											</template>
										</USelectMenu>
									</div>

									<UFormField :label="$t('landing_config.field_cta_text')">
										<UInput
											v-model="draft.cta_text"
											class="w-full"
											:placeholder="$t('landing_config.field_cta_text_placeholder')"
										/>
									</UFormField>
								</div>
							</Fieldset>

							<Fieldset :legend="$t('landing_config.section_content')">
								<div class="flex flex-col gap-4">
									<USwitch
										v-model="draft.about_enabled"
										:label="$t('landing_config.field_about_enabled')"
										:ui="{ label: 'font-semibold' }"
									/>
									<UFormField :label="$t('landing_config.field_about_text')">
										<UTextarea v-model="draft.about_text" class="w-full" :rows="4" />
									</UFormField>
								</div>
							</Fieldset>

							<div class="flex justify-end">
								<UButton color="primary" variant="solid" :loading="isSaving" :label="$t('landing_config.save')" @click="save" />
							</div>
							<p class="text-muted text-xs text-center">{{ $t("landing_config.flat_list_hint") }}</p>
						</div>

						<!-- Live preview -->
						<div class="flex-1">
							<div class="sticky top-4">
								<h3 class="font-semibold mb-2">{{ $t("landing_config.preview_title") }}</h3>
								<p class="text-muted text-xs mb-2">{{ $t("landing_config.preview_hint") }}</p>
								<div class="border border-default rounded-lg overflow-hidden bg-black" style="aspect-ratio: 16 / 10">
									<div class="origin-top-left" style="transform: scale(0.5); width: 200%; height: 200%">
										<component :is="previewComponent" v-if="previewData" :data="previewData" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</template>

				<template #links>
					<div class="mt-4">
						<div class="flex justify-end mb-4">
							<UButton
								color="primary"
								variant="solid"
								icon="lucide:plus"
								size="sm"
								:label="$t('landing_link.create')"
								@click="openCreateLink"
							/>
						</div>

						<div v-if="links.length === 0" class="text-center py-12 text-muted">{{ $t("landing_link.no_links") }}</div>

						<div v-else class="flex flex-col gap-2">
							<div
								v-for="(link, index) in links"
								:key="link.id"
								draggable="true"
								class="flex items-center gap-3 border border-default rounded-lg px-3 py-2 bg-elevated cursor-move"
								@dragstart="dragStart(index)"
								@dragover.prevent
								@drop="dropLink(index)"
							>
								<UIcon name="lucide:grip-vertical" class="text-muted shrink-0" />
								<span class="font-medium w-40 truncate">{{ link.label }}</span>
								<span class="text-muted text-xs flex-1 truncate">{{ link.url }}</span>
								<UBadge color="neutral" size="sm">{{ $t(`landing_link.placement_${link.placement}`) }}</UBadge>
								<USwitch :model-value="link.enabled" @update:model-value="() => toggleLinkEnabled(link)" />
								<UButton icon="lucide:pencil" color="neutral" variant="ghost" size="sm" @click="openEditLink(link)" />
								<UButton icon="lucide:trash" color="error" variant="ghost" size="sm" @click="deleteLink(link)" />
							</div>
						</div>
					</div>
				</template>

				<template #featured>
					<div class="mt-4 flex flex-col gap-6">
						<Fieldset :legend="$t('landing_featured_item.title')">
							<div class="flex flex-col gap-4">
								<USwitch
									:model-value="featuredEnabled"
									:label="$t('landing_featured_item.field_enabled')"
									:ui="{ label: 'font-semibold' }"
									@update:model-value="setFeaturedEnabled"
								/>
								<div class="flex items-center gap-4">
									<label class="font-semibold w-1/3">{{ $t("landing_featured_item.field_mode") }}</label>
									<USelectMenu v-model="selectedFeaturedMode" :items="featuredModeOptions" label-key="label" class="w-2/3">
										<template #item-label="{ item }">{{ item.label }}</template>
									</USelectMenu>
								</div>
								<div v-if="featuredMode === 'automatic'" class="flex items-center gap-4">
									<label class="font-semibold w-1/3">{{ $t("landing_featured_item.field_count") }}</label>
									<UInputNumber
										:model-value="featuredCount"
										:min="3"
										:max="12"
										class="w-1/3"
										@update:model-value="setFeaturedCount"
									/>
									<span class="text-muted text-xs">{{ $t("landing_featured_item.field_count_hint") }}</span>
								</div>
							</div>
						</Fieldset>

						<Fieldset v-if="featuredMode === 'manual'" :legend="$t('landing_featured_item.title')">
							<div class="flex flex-col gap-4">
								<div class="flex gap-2">
									<UInput
										v-model="searchTerm"
										class="flex-1"
										:placeholder="$t('landing_featured_item.search_placeholder')"
										@keyup.enter="runSearch"
									/>
									<UButton color="primary" variant="soft" :loading="isSearching" @click="runSearch">{{
										$t("landing_featured_item.add")
									}}</UButton>
								</div>
								<div v-if="searchResults.length > 0" class="flex flex-col gap-2 max-h-64 overflow-y-auto">
									<div
										v-for="result in searchResults"
										:key="`${result.item_type}-${result.id}`"
										class="flex items-center gap-3 border border-default rounded-lg px-3 py-2"
									>
										<span class="text-xs uppercase text-muted w-14">{{
											$t(`landing_featured_item.type_${result.item_type}`)
										}}</span>
										<span class="flex-1 truncate">{{ result.title }}</span>
										<UButton size="sm" color="primary" variant="soft" @click="addFeaturedItem(result)">
											{{ $t("landing_featured_item.add") }}
										</UButton>
									</div>
								</div>

								<div v-if="featuredItems.length === 0" class="text-center py-8 text-muted">
									{{ $t("landing_featured_item.no_items") }}
								</div>
								<div v-else class="flex flex-col gap-2">
									<div
										v-for="(item, index) in featuredItems"
										:key="item.id"
										draggable="true"
										class="flex items-center gap-3 border border-default rounded-lg px-3 py-2 bg-elevated cursor-move"
										@dragstart="dragStartFeatured(index)"
										@dragover.prevent
										@drop="dropFeatured(index)"
									>
										<UIcon name="lucide:grip-vertical" class="text-muted shrink-0" />
										<span class="text-xs uppercase text-muted w-14">{{
											$t(`landing_featured_item.type_${item.item_type}`)
										}}</span>
										<span class="flex-1 truncate font-mono text-xs">{{ item.item_id }}</span>
										<USwitch :model-value="item.enabled" @update:model-value="() => toggleFeaturedEnabled(item)" />
										<UButton icon="lucide:trash" color="error" variant="ghost" size="sm" @click="deleteFeatured(item)" />
									</div>
								</div>
							</div>
						</Fieldset>
					</div>
				</template>
			</UTabs>
		</template>
	</div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import { storeToRefs } from "pinia";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
import ColorField from "@/v8/components/forms/settings/ColorField.vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import LandingLinkFormDialog from "@/v8/components/forms/landing/LandingLinkFormDialog.vue";
import LandingClassic from "@/v8/views/landing/LandingClassic.vue";
import LandingPortfolio from "@/v8/views/landing/LandingPortfolio.vue";
import LandingMinimal from "@/v8/views/landing/LandingMinimal.vue";
import LandingStudio from "@/v8/views/landing/LandingStudio.vue";
import InitService from "@/services/init-service";
import SettingsService from "@/services/settings-service";
import LandingLinkService from "@/services/landing-link-service";
import LandingFeaturedItemService from "@/services/landing-featured-item-service";
import SearchService from "@/services/search-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useConfirmDialog } from "@/v8/composables/useConfirmDialog";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useLtRorRtL } from "@/utils/Helpers";
import type { TabsItem } from "@nuxt/ui";

const { isLTR } = useLtRorRtL();
const toast = useAppToast();
const { confirm } = useConfirmDialog();
const lycheeStore = useLycheeStateStore();
lycheeStore.load();
const { is_se_enabled, is_se_preview_enabled } = storeToRefs(lycheeStore);
const isSeAvailable = computed(() => is_se_enabled.value || is_se_preview_enabled.value);

const activeTab = ref("settings");
const tabItems: TabsItem[] = [
	{ label: trans("landing_config.tab_settings"), value: "settings", icon: "lucide:cog", slot: "settings" },
	{ label: trans("landing_config.tab_links"), value: "links", icon: "lucide:link", slot: "links" },
	{ label: trans("landing_config.tab_featured"), value: "featured", icon: "lucide:star", slot: "featured" },
];

const loading = ref(true);
const isSaving = ref(false);

type Draft = {
	landing_layout: App.Enum.LandingLayoutType;
	intro_screen_enabled: boolean;
	hero_text_position: App.Enum.LandingTextPosition;
	hero_text_color: string;
	hero_text_opacity: number;
	animation_preset: App.Enum.LandingAnimationPreset;
	about_enabled: boolean;
	about_text: string;
	cta_text: string;
};

const draft = reactive<Draft>({
	landing_layout: "classic",
	intro_screen_enabled: true,
	hero_text_position: "center",
	hero_text_color: "",
	hero_text_opacity: 100,
	animation_preset: "classic_fade",
	about_enabled: false,
	about_text: "",
	cta_text: "",
});

const baseline = ref<App.Http.Resources.GalleryConfigs.LandingPageResource | undefined>(undefined);

function loadSettings(): Promise<void> {
	return SettingsService.getAll().then((response) => {
		const category = response.data.find((c) => c.cat === "Mod Welcome");
		if (!category) {
			return;
		}
		for (const config of category.configs) {
			switch (config.key) {
				case "landing_layout":
					draft.landing_layout = config.value as App.Enum.LandingLayoutType;
					break;
				case "landing_intro_screen_enabled":
					draft.intro_screen_enabled = config.value === "1";
					break;
				case "landing_hero_text_position":
					draft.hero_text_position = config.value as App.Enum.LandingTextPosition;
					break;
				case "landing_hero_text_color":
					draft.hero_text_color = config.value;
					break;
				case "landing_hero_text_opacity":
					draft.hero_text_opacity = parseInt(config.value, 10) || 0;
					break;
				case "landing_animation_preset":
					draft.animation_preset = config.value as App.Enum.LandingAnimationPreset;
					break;
				case "landing_about_enabled":
					draft.about_enabled = config.value === "1";
					break;
				case "landing_about_text":
					draft.about_text = config.value;
					break;
				case "landing_cta_text":
					draft.cta_text = config.value;
					break;
				case "landing_featured_items_enabled":
					featuredEnabled.value = config.value === "1";
					break;
				case "landing_featured_items_mode":
					featuredMode.value = config.value as App.Enum.LandingFeaturedItemsMode;
					break;
				case "landing_featured_items_count":
					featuredCount.value = parseInt(config.value, 10) || 6;
					break;
			}
		}
	});
}

function save(): void {
	isSaving.value = true;
	SettingsService.setConfigs({
		configs: [
			{ key: "landing_layout", value: draft.landing_layout },
			{ key: "landing_intro_screen_enabled", value: draft.intro_screen_enabled ? "1" : "0" },
			{ key: "landing_hero_text_position", value: draft.hero_text_position },
			{ key: "landing_hero_text_color", value: draft.hero_text_color },
			{ key: "landing_hero_text_opacity", value: String(draft.hero_text_opacity) },
			{ key: "landing_animation_preset", value: draft.animation_preset },
			{ key: "landing_about_enabled", value: draft.about_enabled ? "1" : "0" },
			{ key: "landing_about_text", value: draft.about_text },
			{ key: "landing_cta_text", value: draft.cta_text },
		],
	})
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("landing_config.saved"), life: 3000 });
			loadBaseline();
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("landing_config.save_error"), life: 5000 });
		})
		.finally(() => {
			isSaving.value = false;
		});
}

// Layout options, SE-gated
type Option<T> = { label: string; value: T; disabled?: boolean };

const layoutOptions = computed<Option<App.Enum.LandingLayoutType>[]>(() => [
	{ label: "Classic", value: "classic" },
	{ label: "Portfolio", value: "portfolio", disabled: !isSeAvailable.value },
	{ label: "Minimal", value: "minimal", disabled: !isSeAvailable.value },
	{ label: "Studio", value: "studio", disabled: !isSeAvailable.value },
]);
const selectedLayout = computed<Option<App.Enum.LandingLayoutType> | undefined>({
	get: () => layoutOptions.value.find((o) => o.value === draft.landing_layout),
	set: (v) => {
		if (v && !v.disabled) draft.landing_layout = v.value;
	},
});

const positionOptions: Option<App.Enum.LandingTextPosition>[] = [
	{ label: "Top left", value: "top_left" },
	{ label: "Top right", value: "top_right" },
	{ label: "Bottom left", value: "bottom_left" },
	{ label: "Bottom right", value: "bottom_right" },
	{ label: "Center", value: "center" },
];
const selectedPosition = computed<Option<App.Enum.LandingTextPosition> | undefined>({
	get: () => positionOptions.find((o) => o.value === draft.hero_text_position),
	set: (v) => {
		if (v) draft.hero_text_position = v.value;
	},
});

const animationOptions = computed<Option<App.Enum.LandingAnimationPreset>[]>(() => [
	{ label: "None", value: "none" },
	{ label: "Classic fade", value: "classic_fade" },
	{ label: "Zoom in", value: "zoom_in", disabled: !isSeAvailable.value },
	{ label: "Parallax scroll", value: "parallax_scroll", disabled: !isSeAvailable.value },
	{ label: "Slide reveal", value: "slide_reveal", disabled: !isSeAvailable.value },
]);
const selectedAnimation = computed<Option<App.Enum.LandingAnimationPreset> | undefined>({
	get: () => animationOptions.value.find((o) => o.value === draft.animation_preset),
	set: (v) => {
		if (v && !v.disabled) draft.animation_preset = v.value;
	},
});

const heroColorConfig = computed<App.Http.Resources.Models.ConfigResource>(() => ({
	key: "landing_hero_text_color",
	type: "color",
	value: draft.hero_text_color,
	documentation: trans("landing_config.field_hero_text_color"),
	details: "",
	is_expert: false,
	require_se: false,
	order: null,
}));

// Live preview
const previewComponent = computed(() => {
	switch (draft.landing_layout) {
		case "studio":
			return LandingStudio;
		case "minimal":
			return LandingMinimal;
		case "portfolio":
			return LandingPortfolio;
		default:
			return LandingClassic;
	}
});

const previewData = computed<App.Http.Resources.GalleryConfigs.LandingPageResource | undefined>(() => {
	if (!baseline.value) {
		return undefined;
	}
	return {
		...baseline.value,
		layout: draft.landing_layout,
		intro_screen_enabled: draft.intro_screen_enabled,
		hero_text_position: draft.hero_text_position,
		hero_text_color: draft.hero_text_color,
		hero_text_opacity: draft.hero_text_opacity,
		animation_preset: draft.animation_preset,
		about_enabled: draft.about_enabled,
		about_text: draft.about_text,
		cta_text: draft.cta_text,
		links: links.value
			.filter((l) => l.enabled)
			.map((l) => ({ id: l.id, label: l.label, url: l.url, placement: l.placement, open_in_new_tab: l.open_in_new_tab })),
	};
});

function loadBaseline(): Promise<void> {
	return InitService.fetchLandingData().then((response) => {
		baseline.value = response.data;
	});
}

// Links tab
const links = ref<App.Http.Resources.Models.LandingLinkResource[]>([]);
const isLinkFormVisible = ref(false);
const editingLink = ref<App.Http.Resources.Models.LandingLinkResource | undefined>(undefined);
let dragLinkIndex = -1;

function loadLinks(): Promise<void> {
	return LandingLinkService.list().then((response) => {
		links.value = response.data.landing_links;
	});
}

function openCreateLink(): void {
	editingLink.value = undefined;
	isLinkFormVisible.value = true;
}

function openEditLink(link: App.Http.Resources.Models.LandingLinkResource): void {
	editingLink.value = link;
	isLinkFormVisible.value = true;
}

function toggleLinkEnabled(link: App.Http.Resources.Models.LandingLinkResource): void {
	LandingLinkService.patch(link.id, { landing_link_id: link.id, enabled: !link.enabled })
		.then((response) => {
			link.enabled = response.data.enabled;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("landing_link.error_save"), life: 3000 });
		});
}

function deleteLink(link: App.Http.Resources.Models.LandingLinkResource): void {
	confirm({
		title: trans("landing_link.confirm_delete_header"),
		message: trans("landing_link.confirm_delete_message", { label: link.label }),
		severity: "danger",
	}).then((accepted) => {
		if (!accepted) {
			return;
		}
		LandingLinkService.delete(link.id)
			.then(() => {
				toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("landing_link.deleted"), life: 3000 });
				loadLinks();
			})
			.catch(() => {
				toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("landing_link.error_delete"), life: 3000 });
			});
	});
}

function dragStart(index: number): void {
	dragLinkIndex = index;
}

function dropLink(targetIndex: number): void {
	if (dragLinkIndex === -1 || dragLinkIndex === targetIndex) {
		return;
	}
	const reordered = [...links.value];
	const [moved] = reordered.splice(dragLinkIndex, 1);
	reordered.splice(targetIndex, 0, moved);
	links.value = reordered;
	dragLinkIndex = -1;

	LandingLinkService.reorder(reordered.map((l) => l.id))
		.then((response) => {
			links.value = response.data.landing_links;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("landing_link.error_reorder"), life: 3000 });
			loadLinks();
		});
}

// Featured tab
const featuredEnabled = ref(false);
const featuredMode = ref<App.Enum.LandingFeaturedItemsMode>("automatic");
const featuredCount = ref(6);
const featuredItems = ref<App.Http.Resources.Models.LandingFeaturedItemResource[]>([]);
const searchTerm = ref("");
const isSearching = ref(false);
type SearchResult = { item_type: App.Enum.LandingFeaturedItemType; id: string; title: string };
const searchResults = ref<SearchResult[]>([]);
let dragFeaturedIndex = -1;

const featuredModeOptions: Option<App.Enum.LandingFeaturedItemsMode>[] = [
	{ label: trans("landing_featured_item.mode_automatic"), value: "automatic" },
	{ label: trans("landing_featured_item.mode_manual"), value: "manual" },
];
const selectedFeaturedMode = computed<Option<App.Enum.LandingFeaturedItemsMode> | undefined>({
	get: () => featuredModeOptions.find((o) => o.value === featuredMode.value),
	set: (v) => {
		if (v) setFeaturedMode(v.value);
	},
});

function setFeaturedEnabled(value: boolean): void {
	featuredEnabled.value = value;
	SettingsService.setConfigs({ configs: [{ key: "landing_featured_items_enabled", value: value ? "1" : "0" }] }).catch(() => {
		toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("landing_config.save_error"), life: 3000 });
	});
}

function setFeaturedMode(value: App.Enum.LandingFeaturedItemsMode): void {
	featuredMode.value = value;
	SettingsService.setConfigs({ configs: [{ key: "landing_featured_items_mode", value }] }).catch(() => {
		toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("landing_config.save_error"), life: 3000 });
	});
}

function setFeaturedCount(value: number): void {
	featuredCount.value = value;
	SettingsService.setConfigs({ configs: [{ key: "landing_featured_items_count", value: String(value) }] }).catch(() => {
		toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("landing_config.save_error"), life: 3000 });
	});
}

function loadFeaturedItems(): Promise<void> {
	return LandingFeaturedItemService.list().then((response) => {
		featuredItems.value = response.data.landing_featured_items;
	});
}

function runSearch(): void {
	if (searchTerm.value.trim() === "") {
		return;
	}
	isSearching.value = true;
	SearchService.search(undefined, searchTerm.value)
		.then((response) => {
			const albums: SearchResult[] = response.data.albums.map((a) => ({ item_type: "album", id: a.id, title: a.title }));
			const photos: SearchResult[] = response.data.photos.map((p) => ({ item_type: "photo", id: p.id, title: p.title }));
			searchResults.value = [...albums, ...photos];
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("landing_featured_item.error_search"), life: 3000 });
		})
		.finally(() => {
			isSearching.value = false;
		});
}

function addFeaturedItem(result: SearchResult): void {
	LandingFeaturedItemService.create({ item_type: result.item_type, item_id: result.id, sort_order: featuredItems.value.length })
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("landing_featured_item.added"), life: 3000 });
			loadFeaturedItems();
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("landing_featured_item.error_add"), life: 3000 });
		});
}

function toggleFeaturedEnabled(item: App.Http.Resources.Models.LandingFeaturedItemResource): void {
	LandingFeaturedItemService.patch(item.id, { landing_featured_item_id: item.id, enabled: !item.enabled })
		.then((response) => {
			item.enabled = response.data.enabled;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("landing_featured_item.error_add"), life: 3000 });
		});
}

function deleteFeatured(item: App.Http.Resources.Models.LandingFeaturedItemResource): void {
	confirm({
		title: trans("landing_featured_item.confirm_delete_header"),
		message: trans("landing_featured_item.confirm_delete_message", { title: item.item_id }),
		severity: "danger",
	}).then((accepted) => {
		if (!accepted) {
			return;
		}
		LandingFeaturedItemService.delete(item.id)
			.then(() => {
				toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("landing_featured_item.deleted"), life: 3000 });
				loadFeaturedItems();
			})
			.catch(() => {
				toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("landing_featured_item.error_delete"), life: 3000 });
			});
	});
}

function dragStartFeatured(index: number): void {
	dragFeaturedIndex = index;
}

function dropFeatured(targetIndex: number): void {
	if (dragFeaturedIndex === -1 || dragFeaturedIndex === targetIndex) {
		return;
	}
	const reordered = [...featuredItems.value];
	const [moved] = reordered.splice(dragFeaturedIndex, 1);
	reordered.splice(targetIndex, 0, moved);
	featuredItems.value = reordered;
	dragFeaturedIndex = -1;

	LandingFeaturedItemService.reorder(reordered.map((i) => i.id))
		.then((response) => {
			featuredItems.value = response.data.landing_featured_items;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("landing_featured_item.error_reorder"), life: 3000 });
			loadFeaturedItems();
		});
}

onMounted(() => {
	loading.value = true;
	Promise.all([loadSettings(), loadLinks(), loadFeaturedItems(), loadBaseline()]).finally(() => {
		loading.value = false;
	});
});
</script>
