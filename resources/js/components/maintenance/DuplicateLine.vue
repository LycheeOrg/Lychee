<template>
	<div class="hover:bg-primary-emphasis/5">
		<template v-for="duplicate in props.duplicates.data">
			<div
				:class="{
					'flex justify-between items-center hover:text-color-emphasis': true,
					'bg-red-700/10': selectedIds.includes(duplicate.photo_id),
				}"
				@mouseover="hover(duplicate.url ?? '', duplicate.photo_title)"
			>
				<div class="flex-shrink">
					<i
						class="pi pi-trash mr-2"
						:class="{
							'text-red-700': selectedIds.includes(duplicate.photo_id),
							'text-transparent': !selectedIds.includes(duplicate.photo_id),
						}"
					/>
				</div>
				<div class="w-1/3 flex-none flex items-center gap-2 group">
					<router-link :to="{ name: 'album', params: { albumid: duplicate.album_id } }" target="_blank" class="">
						<i class="pi pi-link text-primary-emphasis hover:text-primary-emphasis-alt"></i>
					</router-link>
					<span
						class="w-full inline-block whitespace-nowrap text-ellipsis overflow-hidden cursor-pointer"
						@click="click(duplicate.photo_id)"
					>
						{{ duplicate.album_title }}
					</span>
				</div>
				<div class="w-1/3 flex-none flex gap-2 group">
					<router-link
						:to="{ name: 'photo', params: { albumid: duplicate.album_id, photoid: duplicate.photo_id } }"
						target="_blank"
						class=""
					>
						<i class="pi pi-link text-primary-emphasis hover:text-primary-emphasis-alt"></i>
					</router-link>
					<span
						class="w-full inline-block whitespace-nowrap text-ellipsis overflow-hidden cursor-pointer"
						@click="click(duplicate.photo_id)"
					>
						{{ duplicate.photo_title }}
					</span>
				</div>
				<div class="w-1/4 font-mono text-xs cursor-pointer" @click="click(duplicate.photo_id)">{{ duplicate.checksum.slice(0, 12) }}</div>
			</div>
		</template>
	</div>
</template>
<script setup lang="ts">
import { ref, watch } from "vue";
import { type SplitData } from "@/composables/album/splitter";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";

const toast = useToast();
const props = defineProps<{
	duplicates: SplitData<App.Http.Resources.Models.Duplicates.Duplicate>;
	selectedIds: string[];
}>();

const emits = defineEmits<{
	hover: [src: string, title: string];
	click: [id: string];
}>();

function hover(url: string, title: string) {
	emits("hover", url, title);
}

function click(id: string) {
	emits("click", id);
}

// Warn if all duplicates are selected
// Because this mean that no original is left
const warned = ref(false);

watch(
	() => props.selectedIds,
	(newSelectedIds) => {
		if (newSelectedIds.length === 0) {
			return;
		}

		if (props.duplicates.data.filter((duplicate) => !newSelectedIds.includes(duplicate.photo_id)).length > 0) {
			warned.value = false;
			return;
		}

		if (warned.value === false) {
			toast.add({
				severity: "warn",
				summary: trans("duplicate-finder.warning.no-original-left"),
				detail: trans("duplicate-finder.warning.keep-one"),
				life: 5000,
			});
			warned.value = true;
		}
	},
	{ deep: true },
);
</script>
