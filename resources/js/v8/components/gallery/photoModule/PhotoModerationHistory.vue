<template>
	<!-- Feature 089: admin-only moderation history of the photo; hidden when empty or on error. -->
	<div v-if="latest !== undefined">
		<h2 class="text-highlighted text-base font-bold mt-4 mb-1">
			{{ $t("moderation.history_title") }}
		</h2>
		<div class="mb-1 text-sm">
			<div class="flex items-center gap-1">
				<UIcon
					:name="latest.event === 'held' ? 'lucide:circle-pause' : 'lucide:circle-check'"
					:class="latest.event === 'held' ? 'text-warning' : 'text-success'"
				/>
				<span>{{ moderationEventLabel(latest) }} · {{ moderationEventActor(latest) }}</span>
			</div>
			<div class="text-muted text-xs ms-5">{{ new Date(latest.created_at).toLocaleString() }}</div>
		</div>
		<UButton
			v-if="events.length > 1"
			variant="link"
			color="primary"
			size="xs"
			class="ms-4 px-1"
			:label="$t('moderation.history_show_all', { count: String(events.length) })"
			@click="isHistoryOpen = true"
		/>
		<UModal v-model:open="isHistoryOpen" :title="$t('moderation.history_title')">
			<template #body>
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
			</template>
		</UModal>
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import ModerationService from "@/services/moderation-service";
import { moderationEventActor, moderationEventLabel } from "@/v8/utils/moderationEvents";

const props = defineProps<{
	photoId: string | undefined;
	isOpen: boolean;
}>();

const events = ref<App.Http.Resources.Models.ModerationEventResource[]>([]);
let cachedPhotoId: string | null = null;
const isHistoryOpen = ref(false);

/** The API returns the events newest first; the panel shows only that one, the modal lists them all. */
const latest = computed(() => events.value[0]);

/** Fetched when the panel is open, once per photo. */
watch(
	[() => props.isOpen, () => props.photoId],
	([isOpen, photoId]) => {
		if (!isOpen || photoId === undefined || photoId === cachedPhotoId) {
			return;
		}
		cachedPhotoId = photoId;
		events.value = [];
		isHistoryOpen.value = false;
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
