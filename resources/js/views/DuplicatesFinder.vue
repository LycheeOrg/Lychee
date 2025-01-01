<template>
	<ProgressBar v-if="duplicates === undefined" mode="indeterminate" class="rounded-none absolute w-full" :pt:value:class="'rounded-none'" />
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
		{{ $t("On this page you will find the duplicates pictures found in your database.") }}
	</div>
	<div class="text-muted-color">
		<div class="md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto">
			<ul class="mb-4" v-if="is_se_enabled || is_se_preview_enabled">
				<li class="ml-2 pt-1 flex items-start gap-x-4">
					<Checkbox
						v-model="withChecksumConstraint"
						binary
						inputId="withChecksumConstraint"
						:disabled="is_se_preview_enabled"
						@update:modelValue="fetch"
					/>
					<label for="withChecksumConstraint" :class="{ 'text-muted-color-emphasis': isValid, 'text-warning-600': !isValid }"
						>Checksum must match.</label
					>
				</li>
				<li class="ml-2 pt-1 flex items-start gap-x-4">
					<Checkbox
						v-model="withTitleConstraint"
						binary
						inputId="withTitleConstraint"
						:disabled="is_se_preview_enabled"
						@update:modelValue="fetch"
					/>
					<label for="withTitleConstraint" :class="{ 'text-muted-color-emphasis': isValid, 'text-warning-600': !isValid }"
						>Title must match. <SETag
					/></label>
				</li>
				<li class="ml-2 pt-1 flex items-start gap-x-4">
					<Checkbox
						v-model="withAlbumConstraint"
						binary
						inputId="withAlbumConstraint"
						:disabled="is_se_preview_enabled"
						@update:modelValue="fetch"
					/>
					<label for="withAlbumConstraint" class="text-muted-color">Must be in the same albums <SETag /></label>
				</li>
			</ul>

			<div severity="warn" v-if="!isValid">
				<i class="text-warning-700 pi pi-exclamation-triangle mr-2" /> At least the checksum or title condition must be checked.
			</div>

			<!-- <h2 class="text-muted-color-emphasis text-lg font-bold"><i class="pi pi-question-circle mr-2" />Help</h2>
			<ul>
				<li>Hover ids or titles to highlight related albums.</li>
				<li>
					For your convenience, the <i class="pi pi-angle-up" /> and <i class="pi pi-angle-down" /> buttons allow you to change the values
					of <Left /> and <Right /> by respectively +1 and -1 with propagation.
				</li>
				<li>The <LeftWarn /> and <RightWarn /> indicates that the value of <Left /> (and respectively <Right />) is duplicated somewhere.</li>
				<li>
					Marked <span class="font-bold text-danger-600">Parent Id</span> indicates that the <Left /> and <Right /> do not satisfy the Nest
					Set tree structures. Edit either the <span class="font-bold text-danger-600">Parent Id</span> or the <Left />/<Right /> values.
				</li>
				<li><i class="pi pi-exclamation-triangle mr-2 text-orange-500" />This page will be slow with a large number of albums.</li>
			</ul> -->
		</div>

		<VirtualScroller :items="duplicates" :itemSize="Math.min(50, duplicates?.length ?? 50)" class="h-screen">
			<template v-slot:item="{ item, options }">
				<template v-if="options.first">
					<div
						class="mt-16 pb-2 flex justify-between md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mx-auto hover:bg-primary-emphasis/5 gap-8 items-center font-bold text-lg text-color-emphasis border-b border-b-white/50"
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
				<DuplicateLine :duplicate="item" />
			</template>
		</VirtualScroller>
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

const duplicates = ref<App.Http.Resources.Models.Duplicates.Duplicate[] | undefined>(undefined);
const toast = useToast();
const lycheeStore = useLycheeStateStore();

const withAlbumConstraint = ref(false);
const withChecksumConstraint = ref(true);
const withTitleConstraint = ref(false);
const { is_se_preview_enabled, is_se_enabled } = storeToRefs(lycheeStore);

const isValid = computed(() => withChecksumConstraint.value || withTitleConstraint.value);

function fetch() {
	if (!isValid.value) {
		return;
	}

	duplicates.value = undefined;
	MaintenanceService.getDuplicates(withAlbumConstraint.value, withChecksumConstraint.value, withTitleConstraint.value).then((response) => {
		duplicates.value = response.data;
	});
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
