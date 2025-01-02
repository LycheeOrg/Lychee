<template>
	<ProgressBar v-if="groupedDuplicates === undefined" mode="indeterminate" class="rounded-none absolute w-full" :pt:value:class="'rounded-none'" />
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ $t("maintenance.title") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<div class="text-muted-color text-center mt-2 p-2">
		<p class="mb-4">
			{{ $t("On this page you will find the duplicates pictures found in your database.") }}
		</p>
		<p v-if="isValid && groupedDuplicates !== undefined">
			<span class="text-muted-color-emphasis">{{ duplicates?.length }}</span> duplicates found!
		</p>
		<p class="text-muted-color-emphasis" v-if="!isValid">
			<i class="text-warning-700 pi pi-exclamation-triangle mr-2" /> At least the checksum or title condition must be checked.
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
						Checksum must match.
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
						Title must match.
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
					<label for="withAlbumConstraint" class="text-muted-color"> Must be in the same albums </label>
					<SETag />
				</li>
			</ul>
		</div>
		<div class="flex justify-between md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mx-auto gap-4 xl:gap-8">
			<div class="w-1/4 flex flex-col flex-none">
				<img :src="hoverImgSrc" class="w-full" />
				<div class="text-center mt-2 font-bold text-muted-color-emphasis">
					<span class="inline-block w-full text-ellipsis text-nowrap whitespace-nowrap overflow-hidden">{{ hoverTitle }}</span>
				</div>
			</div>
			<VirtualScroller :items="groupedDuplicates" :itemSize="10" class="h-screen w-full">
				<template v-slot:item="{ item, options }">
					<template v-if="options.first">
						<div
							class="pb-2 flex justify-between md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mx-auto hover:bg-primary-emphasis/5 gap-8 items-center font-bold text-lg text-color-emphasis border-b border-b-white/50"
						>
							<div class="w-1/4">Album</div>
							<div class="w-1/4">Photo</div>
							<div class="w-1/4">Checksum</div>
							<!-- <div class="flex w-1/4 justify-between">
								<div class="w-full">Id</div>
								<div class="w-full text-right">Parent Id</div>
							</div> -->
						</div>
					</template>
					<DuplicateLine :duplicates="item" @hover="onHover" />
				</template>
			</VirtualScroller>
		</div>
	</div>
	<ScrollTop />
</template>
<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import MaintenanceService from "@/services/maintenance-service";
import Toolbar from "primevue/toolbar";
import ProgressBar from "primevue/progressbar";
import Button from "primevue/button";
import ScrollTop from "primevue/scrolltop";
import VirtualScroller from "primevue/virtualscroller";
import { useToast } from "primevue/usetoast";
import Checkbox from "primevue/checkbox";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import DuplicateLine from "@/components/maintenance/DuplicateLine.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import SETag from "@/components/icons/SETag.vue";
import { type SplitData, useSplitter } from "@/composables/album/splitter";

const duplicates = ref<App.Http.Resources.Models.Duplicates.Duplicate[] | undefined>(undefined);
const groupedDuplicates = ref<SplitData<App.Http.Resources.Models.Duplicates.Duplicate>[] | undefined>(undefined);
const toast = useToast();
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

function fetch() {
	if (!isValid.value) {
		return;
	}

	duplicates.value = undefined;
	groupedDuplicates.value = undefined;

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
