<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("insights.title") }}
	</UHeader>
	<div class="w-full max-w-6xl mx-auto px-4 py-4 flex flex-col gap-4">
		<UCard v-if="is_se_preview_enabled" class="text-center text-highlighted">
			<div v-html="$t('insights.preview_text')" />
		</UCard>
		<div class="flex flex-wrap items-center gap-3">
			<USelect
				v-if="scopeItems.length > 1"
				v-model="scope"
				:items="scopeItems"
				:disabled="albumId !== ALL_ALBUMS"
				size="sm"
				class="w-48"
				:aria-label="$t('insights.scope.label')"
			/>
			<USelectMenu
				v-if="albumItems.length > 1"
				v-model="albumId"
				:items="albumItems"
				value-key="value"
				size="sm"
				class="w-64"
				:aria-label="$t('insights.album.label')"
			/>
			<InsightsToggle v-model="period" :options="periods" />
			<USelect v-if="period === 'year'" v-model="year" :items="yearItems" size="sm" class="w-28" :aria-label="$t('insights.period.year')" />
			<template v-if="period === 'range'">
				<UInput v-model="from" type="date" size="sm" :aria-label="$t('insights.period.from')" />
				<span class="text-muted">–</span>
				<UInput v-model="to" type="date" size="sm" :aria-label="$t('insights.period.to')" />
			</template>
		</div>
		<template v-if="insights !== undefined && insights.overview.total > 0">
			<OverviewSection :overview="insights.overview" :newest="insights.time_span.last" :is-library="period === 'library'" />
			<StorageSection
				:storage="insights.storage"
				:show-details="showStorageDetails"
				:album-id="albumId === ALL_ALBUMS ? null : albumId"
				:by-owner="userStore.isAdmin && scope === ALL && albumId === ALL_ALBUMS"
				:show-space="period === 'library' && !is_se_preview_enabled"
				:space-owner="spaceOwner"
			/>
			<div class="grid gap-4 md:grid-cols-2">
				<PeopleSection v-if="insights.people.has_faces" :people="insights.people" />
				<PlacesSection :places="insights.places" :class="{ 'md:col-span-2': !insights.people.has_faces }" />
			</div>
			<TimeSpanSection :time-span="insights.time_span" />
			<TimelineSection :timeline="insights.timeline" :calendar="insights.calendar" />
			<CalendarSection :calendar="insights.calendar" :year="period === 'year' ? year : undefined" />
			<RhythmSection :rhythm="insights.rhythm" />
			<DevicesSection :devices="insights.devices" />
			<FocalSection :focal="insights.exposure.focal" :per-device="insights.devices.focal_lengths" />
			<ExposureSection :exposure="insights.exposure" />
			<FormatsSection :formats="insights.formats" />
		</template>
		<UCard v-else-if="insights !== undefined" class="text-center text-muted">{{ $t("insights.empty") }}</UCard>
		<UCard v-else-if="failed" class="text-center text-muted">{{ $t("insights.error") }}</UCard>
		<div v-else class="flex justify-center py-24" role="status" :aria-label="$t('insights.title')">
			<LycheeLoadingIcon class="text-6xl" />
		</div>
	</div>
</template>
<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import { trans } from "laravel-vue-i18n";
import InsightsService, { type InsightsQuery } from "@/services/insights-service";
import UserManagementService from "@/services/user-management-service";
import { usePreviewData } from "@/composables/preview/getPreviewInfo";
import { useUserStore } from "@/stores/UserState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { definePanelShortcuts } from "@/v8/composables/usePanelShortcuts";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import InsightsToggle from "@/v8/components/insights/InsightsToggle.vue";
import OverviewSection from "@/v8/components/insights/OverviewSection.vue";
import StorageSection from "@/v8/components/insights/StorageSection.vue";
import PeopleSection from "@/v8/components/insights/PeopleSection.vue";
import PlacesSection from "@/v8/components/insights/PlacesSection.vue";
import TimeSpanSection from "@/v8/components/insights/TimeSpanSection.vue";
import CalendarSection from "@/v8/components/insights/CalendarSection.vue";
import RhythmSection from "@/v8/components/insights/RhythmSection.vue";
import DevicesSection from "@/v8/components/insights/DevicesSection.vue";
import ExposureSection from "@/v8/components/insights/ExposureSection.vue";
import FocalSection from "@/v8/components/insights/FocalSection.vue";
import FormatsSection from "@/v8/components/insights/FormatsSection.vue";
import TimelineSection from "@/v8/components/insights/TimelineSection.vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import StatisticsService from "@/services/statistics-service";

/** "mine", "all" (whole instance) or a user id. */
type Scope = string;

const MINE: Scope = "mine";
const ALL: Scope = "all";
const ALL_ALBUMS = "__all__";

const router = useRouter();
const userStore = useUserStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.load();
const { is_se_preview_enabled, are_nsfw_visible } = storeToRefs(lycheeStore);

const insights = ref<App.Http.Resources.Insights.InsightsResource | undefined>(undefined);
const failed = ref(false);
const users = ref<App.Http.Resources.Models.UserManagementResource[]>([]);
const albums = ref<App.Http.Resources.Statistics.Album[]>([]);
const albumId = ref(ALL_ALBUMS);

const scope = ref<Scope>(MINE);
const period = ref<App.Enum.InsightsPeriodType>("library");
const year = ref<number | undefined>(undefined);
const from = ref("");
const to = ref("");
const years = ref<number[]>([]);

const periods: { value: App.Enum.InsightsPeriodType; label: string }[] = [
	{ value: "library", label: trans("insights.period.library") },
	{ value: "year", label: trans("insights.period.year") },
	{ value: "range", label: trans("insights.period.range") },
];

const scopeItems = computed(() => {
	if (!userStore.isAdmin || is_se_preview_enabled.value) {
		return [];
	}
	return [
		{ value: MINE, label: trans("insights.scope.mine") },
		{ value: ALL, label: trans("insights.scope.whole_instance") },
		...users.value.filter((user) => user.id !== userStore.user?.id).map((user) => ({ value: String(user.id), label: user.username })),
	];
});

/** Albums the viewer may scope to (own albums, or every album for administrators), indented by depth (FR-085-16). */
const albumItems = computed(() => {
	if (is_se_preview_enabled.value || albums.value.length === 0) {
		return [];
	}
	const rights: number[] = [];
	const items = [...albums.value]
		.sort((a, b) => a.left - b.left)
		.filter((album) => !album.is_nsfw || are_nsfw_visible.value)
		.map((album) => {
			while (rights.length > 0 && rights[rights.length - 1] < album.left) {
				rights.pop();
			}
			const depth = rights.length;
			rights.push(album.right);
			const owner = userStore.isAdmin ? ` (${album.username})` : "";
			return { value: album.id, label: `${"\u00a0\u00a0".repeat(depth)}${album.title}${owner}` };
		});
	return [{ value: ALL_ALBUMS, label: trans("insights.album.all") }, ...items];
});

const yearItems = computed(() => [...years.value].reverse().map((y) => ({ value: y, label: String(y) })));

/**
 * The storage meter, space diagram and album table answer for an album tree, the own library, or every owner when asked by an
 * administrator (FR-085-07, FR-085-20).
 */
const showStorageDetails = computed(
	() =>
		period.value === "library" &&
		!is_se_preview_enabled.value &&
		(albumId.value !== ALL_ALBUMS || (userStore.isAdmin ? scope.value === ALL : scope.value === MINE)),
);

/** Owner whose albums the space diagram keeps: the endpoint returns every owner to administrators. */
const spaceOwner = computed(() => {
	if (albumId.value !== ALL_ALBUMS || scope.value === ALL || !userStore.isAdmin) {
		return null;
	}
	if (scope.value === MINE) {
		return userStore.user?.username ?? null;
	}
	return users.value.find((user) => String(user.id) === scope.value)?.username ?? null;
});

function query(): InsightsQuery | undefined {
	const owner: Partial<InsightsQuery> = scope.value === ALL ? { whole_instance: 1 } : scope.value === MINE ? {} : { owner_id: Number(scope.value) };
	const scoped: Partial<InsightsQuery> = albumId.value === ALL_ALBUMS ? owner : { album_id: albumId.value };

	switch (period.value) {
		case "library":
			return { ...scoped, period: "library" };
		case "year":
			return year.value === undefined ? undefined : { ...scoped, period: "year", year: year.value };
		case "range":
			return from.value === "" || to.value === "" || from.value > to.value
				? undefined
				: { ...scoped, period: "range", from: from.value, to: to.value };
	}
}

let request = 0;

function load() {
	if (is_se_preview_enabled.value) {
		insights.value = usePreviewData().getInsightsData();
		years.value = insights.value.years;
		return;
	}

	const params = query();
	if (params === undefined) {
		return;
	}

	const current = ++request;
	insights.value = undefined;
	failed.value = false;
	InsightsService.get(params)
		.then((response) => {
			if (current !== request) {
				return;
			}
			insights.value = response.data;
			years.value = response.data.years;
		})
		.catch(() => {
			// Leave the loading state; the global axios handler reports the error itself.
			if (current === request) {
				failed.value = true;
			}
		});
}

watch(period, (value) => {
	if (value === "year" && year.value === undefined) {
		year.value = years.value[years.value.length - 1] ?? new Date().getFullYear();
	}
});

watch([scope, albumId, period, year, from, to], load);

onMounted(async () => {
	await userStore.load();
	if (!userStore.isLoggedIn) {
		router.push({ name: "home" });
		return;
	}
	if (userStore.isAdmin && !is_se_preview_enabled.value) {
		UserManagementService.get().then((response) => (users.value = response.data));
	}
	if (!is_se_preview_enabled.value) {
		StatisticsService.getAlbumSpace().then((response) => (albums.value = response.data));
	}
	load();
});

definePanelShortcuts({
	h: () => (are_nsfw_visible.value = !are_nsfw_visible.value),
});
</script>
