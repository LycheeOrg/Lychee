<template>
	<USelectMenu
		id="targetAlbum"
		v-model="selectedTarget"
		class="w-full"
		:placeholder="$t('dialogs.target_album.placeholder')"
		:loading="options === undefined"
		:items="options"
		:virtualize="{ estimateSize: 32, overscan: 50 }"
		label-key="original"
		@update:model-value="selected"
	>
		<template #item-leading="{ item }">
			<Thumb v-if="item.id !== null" :album-id="item.id" :photo-id="item.coverId ?? null" type="small" class="w-4 rounded-sm" />
			<img v-else :src="item.thumb" alt="poster" class="w-4 rounded-sm" />
		</template>
		<template #item-label="{ item }">
			{{ item.title }}
		</template>
	</USelectMenu>
</template>
<script setup lang="ts">
import { ref, watch } from "vue";
import { trans } from "laravel-vue-i18n";
import { useAlbumListStore } from "@/stores/AlbumListState";
import Thumb from "@/v8/components/thumbs/Thumb.vue";
import { useImageHelpers } from "@/utils/Helpers";

type TargetOption = App.Http.Resources.Models.TargetAlbumResource & { coverId?: string | null };

const props = defineProps<{
	albumIds: string[] | undefined;
}>();

const albumIds = ref<string[] | null>(props.albumIds ?? null);
const emits = defineEmits<{
	selected: [target: App.Http.Resources.Models.TargetAlbumResource];
	"no-target": [];
}>();

const albumListStore = useAlbumListStore();
const { getNoImageIcon } = useImageHelpers();

const options = ref<TargetOption[] | undefined>(undefined);
const selectedTarget = ref<TargetOption | undefined>(undefined);

function load() {
	albumListStore.ensureLoaded().then(() => {
		const roots = albumIds.value ?? [];
		const excluded = albumListStore.getExcludedTargetIds(roots);
		// Only albums the viewer may edit can receive content.
		const rows: TargetOption[] = albumListStore.rows
			.filter((row) => row.canEdit && !excluded.has(row.id))
			.map((row) => {
				const breadcrumb = albumListStore.buildBreadcrumb(row.id);
				return {
					id: row.id,
					title: breadcrumb,
					original: row.title,
					short_title: breadcrumb,
					thumb: "",
					coverId: row.coverId,
				};
			});

		// Mirrors AlbumController::getTargetListAlbums's `$parent_id = $albums->first()->parent_id`
		// rule: only the first selected album decides whether "move to root" is offered, even for
		// a multi-album merge selection.
		if (roots.length > 0 && !albumListStore.isTopLevel(roots[0])) {
			rows.unshift({
				id: null,
				title: trans("gallery.root"),
				original: trans("gallery.root"),
				short_title: trans("gallery.root"),
				thumb: getNoImageIcon(),
				coverId: null,
			});
		}

		options.value = rows;
		if (options.value.length === 0) {
			emits("no-target");
		}
	});
}

load();

function selected() {
	if (selectedTarget.value === undefined) {
		return;
	}

	emits("selected", selectedTarget.value);
}

watch(
	() => props.albumIds,
	(newAlbumId, _oldAlbumId) => {
		albumIds.value = newAlbumId ?? null;
		load();
	},
);
</script>
