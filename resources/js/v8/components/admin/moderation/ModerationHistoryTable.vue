<template>
	<!-- Loading initial -->
	<div v-if="loading && events.length === 0" class="flex justify-center py-12">
		<LycheeLoadingIcon fast class="text-3xl" />
	</div>

	<!-- Empty state -->
	<div v-else-if="!loading && events.length === 0" class="text-center py-12">
		<div class="text-muted mb-4">
			<UIcon name="lucide:check-circle" class="text-4xl" />
		</div>
		<p class="text-muted">{{ $t("moderation.no_history") }}</p>
	</div>

	<UTable v-if="events.length > 0" :data="events" :columns="columns" sticky class="max-h-[65vh] text-sm">
		<template #thumbnail-cell="{ row }">
			<img
				v-if="row.original.thumb_url"
				:src="row.original.thumb_url"
				:alt="row.original.photo_title"
				class="w-16 min-w-16 h-16 object-cover rounded cursor-pointer hover:opacity-80"
				@click="emits('open-photo', row.original.photo_id)"
			/>
			<UIcon v-else name="lucide:image" class="text-2xl text-muted" />
		</template>

		<template #event-cell="{ row }">
			<UBadge :color="row.original.event === 'held' ? 'warning' : 'success'" class="text-xs">
				{{ moderationEventLabel(row.original) }}
			</UBadge>
		</template>

		<template #by-cell="{ row }">
			{{ moderationEventActor(row.original) }}
		</template>

		<template #created_at-cell="{ row }">
			<span class="whitespace-nowrap">{{ new Date(row.original.created_at).toLocaleString() }}</span>
		</template>

		<template #body-bottom>
			<tr>
				<td colspan="6">
					<div ref="sentinel" class="flex justify-center py-4">
						<LycheeLoadingIcon fast v-if="loading && events.length > 0" class="text-2xl" />
					</div>
				</td>
			</tr>
		</template>
	</UTable>
</template>
<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from "vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import ModerationService from "@/services/moderation-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { moderationEventActor, moderationEventLabel } from "@/v8/utils/moderationEvents";
import { trans } from "laravel-vue-i18n";
import UBadge from "@nuxt/ui/components/Badge.vue";
import type { TableColumn } from "@nuxt/ui";

type ModerationEvent = App.Http.Resources.Models.ModerationEventResource;

const emits = defineEmits<{
	"open-photo": [photoId: string];
}>();

const toast = useAppToast();

const loading = ref(false);
const events = ref<ModerationEvent[]>([]);
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = 30;
const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

const columns: TableColumn<ModerationEvent>[] = [
	{ id: "thumbnail", header: trans("moderation.col_photo") },
	{ accessorKey: "photo_title", header: trans("moderation.col_title") },
	{ accessorKey: "owner_username", header: trans("moderation.col_owner") },
	{ id: "event", header: trans("moderation.col_event") },
	{ id: "by", header: trans("moderation.col_by") },
	{ id: "created_at", header: trans("moderation.col_date") },
];

function load(page: number) {
	if (loading.value || page > lastPage.value) {
		return;
	}
	loading.value = true;
	ModerationService.history(page, perPage)
		.then((response) => {
			events.value = page === 1 ? response.data.events : [...events.value, ...response.data.events];
			currentPage.value = response.data.current_page;
			lastPage.value = response.data.last_page;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("moderation.no_history"), life: 3000 });
		})
		.finally(() => {
			loading.value = false;
		});
}

onMounted(() => {
	load(1);
	observer = new IntersectionObserver(
		(entries) => {
			if (entries[0].isIntersecting && !loading.value && currentPage.value < lastPage.value) {
				load(currentPage.value + 1);
			}
		},
		{ threshold: 0.1 },
	);
});

// The sentinel only mounts once the table (and its `body-bottom` slot) renders.
watch(sentinel, (el) => {
	if (el) {
		observer?.observe(el);
	}
});

onUnmounted(() => {
	observer?.disconnect();
});
</script>
