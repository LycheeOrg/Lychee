<template>
	<ProgressBar v-if="groupedDuplicates === undefined" mode="indeterminate" class="rounded-none absolute w-full" :pt:value:class="'rounded-none'" />
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ $t("duplicate-finder.title") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<div class="text-muted-color text-center mt-2 p-2">
		<p class="mb-4">
			{{ $t("duplicate-finder.intro") }}
		</p>
		<p v-if="isValid && groupedDuplicates !== undefined">
			<span class="text-muted-color-emphasis">{{ duplicates?.length }}</span> {{ $t("duplicate-finder.found") }}
		</p>
		<p class="text-muted-color-emphasis" v-if="!isValid">
			<i class="text-warning-700 pi pi-exclamation-triangle mr-2" /> {{ $t("duplicate-finder.invalid-search") }}
		</p>
	</div>
	<div class="text-muted-color">
		<div class="md:max-w-md mt-2 mb-16 mx-auto">
			<ul class="mb-4" v-if="is_se_enabled || is_se_preview_enabled">
				<li class="ml-2 pt-1 flex items-center gap-x-4">
					<Checkbox
						v-model="withChecksumConstraint"
						binary
						inputId="withChecksumConstraint"
						:disabled="is_se_preview_enabled"
						@update:modelValue="fetch"
					/>
					<label for="withChecksumConstraint" :class="{ 'text-muted-color-emphasis': isValid, 'text-warning-600': !isValid }">
						{{ $t("duplicate-finder.checksum-must-match") }}
					</label>
				</li>
				<li class="ml-2 pt-1 flex items-center gap-x-4">
					<Checkbox
						v-model="withTitleConstraint"
						binary
						inputId="withTitleConstraint"
						:disabled="is_se_preview_enabled"
						@update:modelValue="fetch"
					/>
					<label for="withTitleConstraint" :class="{ 'text-muted-color-emphasis': isValid, 'text-warning-600': !isValid }">
						{{ $t("duplicate-finder.title-must-match") }}
					</label>
					<SETag />
				</li>
				<li class="ml-2 pt-1 flex items-center gap-x-4">
					<Checkbox
						v-model="withAlbumConstraint"
						binary
						inputId="withAlbumConstraint"
						:disabled="is_se_preview_enabled"
						@update:modelValue="fetch"
					/>
					<label for="withAlbumConstraint" class="text-muted-color"> {{ $t("duplicate-finder.must-be-in-same-album") }} </label>
					<SETag />
				</li>
			</ul>
		</div>
		<div class="flex justify-between md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mx-auto gap-4 xl:gap-8">
			<div class="w-1/4 flex-none"></div>
			<div class="pb-2 w-full flex justify-between items-center font-bold text-lg text-color-emphasis border-b border-b-white/50">
				<div class=""><i class="pi pi-trash text-transparent mr-2" /></div>
				<div class="w-1/3">{{ $t("duplicate-finder.columns.album") }}</div>
				<div class="w-1/3">{{ $t("duplicate-finder.columns.photo") }}</div>
				<div class="w-1/4">{{ $t("duplicate-finder.columns.checksum") }}</div>
			</div>
		</div>
		<div class="flex justify-between md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mx-auto gap-4 xl:gap-8">
			<div class="w-1/4 flex flex-col flex-none">
				<Button
					severity="danger"
					class="w-full font-bold border-none mb-4"
					@click="isDeleteVisible = true"
					:disabled="selectedIds.length === 0"
					>{{ $t("duplicate-finder.delete-selected") }}</Button
				>

				<img :src="hoverImgSrc" class="w-full" />
				<div class="text-center mt-2 font-bold text-muted-color-emphasis">
					<span class="inline-block w-full text-ellipsis text-nowrap whitespace-nowrap overflow-hidden">{{ hoverTitle }}</span>
				</div>
			</div>
			<VirtualScroller :items="groupedDuplicates" :itemSize="50" class="h-screen w-full">
				<template v-slot:item="{ item, options }">
					<DuplicateLine :duplicates="item" :selected-ids="selectedIds" @hover="onHover" @click="onClick" />
				</template>
			</VirtualScroller>
		</div>
	</div>
	<DeleteDialog v-model:visible="isDeleteVisible" :parent-id="undefined" :photo-ids="selectedIds" @deleted="onDeleted" />
</template>
<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import MaintenanceService from "@/services/maintenance-service";
import Toolbar from "primevue/toolbar";
import ProgressBar from "primevue/progressbar";
import Button from "primevue/button";
import VirtualScroller from "primevue/virtualscroller";
import Checkbox from "primevue/checkbox";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import DuplicateLine from "@/components/maintenance/DuplicateLine.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import SETag from "@/components/icons/SETag.vue";
import { type SplitData, useSplitter } from "@/composables/album/splitter";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";

const duplicates = ref<App.Http.Resources.Models.Duplicates.Duplicate[] | undefined>(undefined);
const groupedDuplicates = ref<SplitData<App.Http.Resources.Models.Duplicates.Duplicate>[] | undefined>(undefined);
const isDeleteVisible = ref(false);
const selectedIds = ref<string[]>([]);
const lycheeStore = useLycheeStateStore();

const { spliter } = useSplitter();

const withAlbumConstraint = ref(false);
const withChecksumConstraint = ref(true);
const withTitleConstraint = ref(false);
const { is_se_preview_enabled, is_se_enabled } = storeToRefs(lycheeStore);

const hoverImgSrc = ref("");
const hoverTitle = ref("");
const isValid = computed(() => withChecksumConstraint.value || withTitleConstraint.value);

function onHover(src: string, title: string) {
	hoverImgSrc.value = src;
	hoverTitle.value = title;
}

function onClick(id: string) {
	const index = selectedIds.value.indexOf(id);
	if (index === -1) {
		selectedIds.value.push(id);
	} else {
		selectedIds.value.splice(index, 1);
	}
}

function onDeleted() {
	// Remove the deleted photos (no need to fetch data again).
	duplicates.value = duplicates.value?.filter((duplicate) => !selectedIds.value.includes(duplicate.photo_id));
	groupData();

	// Remove groups with only one element => no duplicates
	groupedDuplicates.value = groupedDuplicates.value?.filter((group) => group.data.length > 1);
}

function fetch() {
	if (!isValid.value) {
		return;
	}

	duplicates.value = undefined;
	groupedDuplicates.value = undefined;
	selectedIds.value = [];
	hoverImgSrc.value = "";
	hoverTitle.value = "";

	MaintenanceService.getDuplicates(withAlbumConstraint.value, withChecksumConstraint.value, withTitleConstraint.value).then((response) => {
		duplicates.value = response.data;
		groupData();
	});
}

function groupData() {
	if (duplicates.value === undefined) {
		return;
	}

	if (withChecksumConstraint.value) {
		groupedDuplicates.value = spliter(
			duplicates.value,
			(a) => a.checksum,
			(a) => a.checksum,
		);
	} else {
		groupedDuplicates.value = spliter(
			duplicates.value,
			(a) => a.photo_title,
			(a) => a.photo_title,
		);
	}
}

onMounted(() => {
	fetch();
});
</script>
<style lang="css">
/* Kill the border of ScrollTop */
.p-scrolltop {
	border: none;
}
</style>
