<template>
	<!-- Feature 089: admin-only moderation history of the photo; hidden when empty or on error. -->
	<div v-if="events.length > 0">
		<h2 class="text-highlighted text-base font-bold mt-4 mb-1">
			{{ $t("moderation.history_title") }}
		</h2>
		<ul class="list-none p-0 m-0">
			<li v-for="event in events" :key="event.id" class="mb-1 text-sm">
				<div class="flex items-center gap-1">
					<UIcon
						:name="event.event === 'held' ? 'lucide:circle-pause' : 'lucide:circle-check'"
						:class="event.event === 'held' ? 'text-warning' : 'text-success'"
					/>
					<span>{{ moderationEventLabel(event) }} · {{ moderationEventActor(event) }}</span>
				</div>
				<div class="text-muted text-xs ms-5">{{ new Date(event.created_at).toLocaleString() }}</div>
			</li>
		</ul>
	</div>
</template>
<script setup lang="ts">
import { ref, watch } from "vue";
import ModerationService from "@/services/moderation-service";
import { moderationEventActor, moderationEventLabel } from "@/v8/utils/moderationEvents";

const props = defineProps<{
	photoId: string | undefined;
	isOpen: boolean;
}>();

const events = ref<App.Http.Resources.Models.ModerationEventResource[]>([]);
let cachedPhotoId: string | null = null;

/** Fetched when the panel is open, once per photo. */
watch(
	[() => props.isOpen, () => props.photoId],
	([isOpen, photoId]) => {
		if (!isOpen || photoId === undefined || photoId === cachedPhotoId) {
			return;
		}
		cachedPhotoId = photoId;
		events.value = [];
		ModerationService.photoHistory(photoId)
			.then((response) => {
				if (cachedPhotoId === photoId) {
					events.value = response.data;
				}
			})
			.catch(() => {
				// Only the current photo is affected, and it is fetched again next time.
				if (cachedPhotoId === photoId) {
					events.value = [];
					cachedPhotoId = null;
				}
			});
	},
	{ immediate: true },
);
</script>
