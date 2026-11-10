<template>
	<InsightsSection :title="$t('insights.overview.eyebrow')">
		<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-5">
			<div class="flex items-center justify-between gap-4 sm:col-span-2">
				<div class="flex flex-col gap-2 min-w-0">
					<span class="text-5xl font-bold text-highlighted">{{ formatNumber(props.overview.total) }}</span>
					<div class="flex flex-wrap gap-2">
						<UBadge color="neutral" variant="soft" :label="`${formatNumber(props.overview.photos)} ${$t('insights.overview.photos')}`" />
						<UBadge color="neutral" variant="soft" :label="`${formatNumber(props.overview.videos)} ${$t('insights.overview.videos')}`" />
						<UBadge
							v-if="props.overview.others > 0"
							color="neutral"
							variant="soft"
							:label="`${formatNumber(props.overview.others)} ${$t('insights.overview.others')}`"
						/>
					</div>
				</div>
				<img
					v-if="props.newest?.thumb_url"
					:src="props.newest.thumb_url"
					alt=""
					class="size-20 shrink-0 rounded-lg object-cover shadow-md shadow-black/25"
				/>
			</div>
			<InsightsStat :label="$t('insights.overview.highlighted')" :value="formatNumber(props.overview.highlighted)" />
			<InsightsStat
				:label="$t('insights.overview.albums')"
				:value="formatNumber(props.overview.albums)"
				:note="props.isLibrary ? $t('insights.overview.albums_library') : $t('insights.overview.albums_period')"
			/>
			<InsightsStat :label="$t('insights.overview.without_album')" :value="formatNumber(props.overview.photos_without_album)" />
		</div>
	</InsightsSection>
</template>
<script setup lang="ts">
import { formatNumber } from "@/v8/utils/insights/format";
import InsightsSection from "./InsightsSection.vue";
import InsightsStat from "./InsightsStat.vue";

const props = defineProps<{
	overview: App.Http.Resources.Insights.OverviewData;
	newest: App.Http.Resources.Insights.CapturePointData | null;
	isLibrary: boolean;
}>();
</script>
