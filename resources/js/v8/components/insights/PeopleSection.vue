<template>
	<InsightsSection :title="$t('insights.people.title')">
		<div class="grid gap-6 grid-cols-2">
			<InsightsStat :label="$t('insights.people.with_people')" :value="formatNumber(props.people.photos_with_people)" />
			<InsightsStat :label="$t('insights.people.people')" :value="formatNumber(props.people.people)" />
			<InsightsStat :label="$t('insights.people.faces')" :value="formatNumber(props.people.faces)" />
			<InsightsStat
				:label="$t('insights.people.per_photo')"
				:value="props.people.faces_per_photo === null ? '—' : formatNumber(props.people.faces_per_photo, 1)"
			/>
		</div>
	</InsightsSection>
</template>
<script setup lang="ts">
import { formatNumber } from "@/v8/utils/insights/format";
import InsightsSection from "./InsightsSection.vue";
import InsightsStat from "./InsightsStat.vue";

const props = defineProps<{ people: App.Http.Resources.Insights.PeopleData }>();
</script>
