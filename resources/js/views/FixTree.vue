<template>
	<ProgressBar v-if="albums === undefined" mode="indeterminate" class="rounded-none absolute w-full" :pt:value:class="'rounded-none'" />
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ $t("maintenance.title") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<div class="text-muted-color">
		<div class="text-center mt-2 p-2">
			This page allows you to re-order and fix your albums manually.<br />
			Before any modifications, we strongly recommend you to read about Nested Set tree structures.<br />
			<span class="text-danger-700 text-lg font-bold"
				><i class="pi text-danger-700 pi-exclamation-triangle mr-2" />You can really break your Lychee installation here, modify values at
				your own risks.</span
			>
		</div>
		<div class="md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto">
			<h2 class="text-muted-color-emphasis text-lg font-bold"><i class="pi pi-question-circle mr-2" />Help</h2>
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
			</ul>
		</div>

		<div
			class="md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto sticky z-30 w-full top-0 flex h-11 justify-center"
			v-if="albums !== undefined"
		>
			<Button text severity="secondary" class="border-none px-8 font-bold" @click="fetch">Reset</Button>
			<Button severity="warn" class="border-none px-8 font-bold" @click="check">Check</Button>
			<Button severity="danger" class="border-none px-8 font-bold" @click="apply"
				><i class="pi pi-exclamation-triangle" v-if="!isValidated" />Apply</Button
			>
		</div>
		<VirtualScroller :items="albums" :itemSize="50" class="h-screen">
			<template v-slot:item="{ item, options }">
				<template v-if="options.first">
					<div
						class="mt-16 pb-2 flex justify-between md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mx-auto hover:bg-primary-emphasis/5 gap-8 items-center font-bold text-lg text-color-emphasis border-b border-b-white/50"
					>
						<div class="w-1/2">Title</div>
						<div class="flex w-1/4 gap-4">
							<div class="w-full pl-4">Left</div>
							<div class="w-full pl-4">Right</div>
						</div>
						<div class="flex w-1/4 justify-between">
							<div class="w-full">Id</div>
							<div class="w-full text-right">Parent Id</div>
						</div>
					</div>
				</template>
				<FixTreeLine
					v-model:lft="item._lft"
					v-model:rgt="item._rgt"
					v-model:parent-id="item.parent_id"
					:parent-id-options="albumIds"
					:album="item"
					:is-hover-id="hoverId === item.trimmedId"
					:is-hover-parent="hoverId === item.trimmedParentId"
					@hover-id="setHoverId"
					@decrement-lft="decrementLft(item.id)"
					@increment-lft="incrementLft(item.id)"
					@decrement-rgt="decrementRgt(item.id)"
					@increment-rgt="incrementRgt(item.id)"
				/>
			</template>
		</VirtualScroller>
	</div>
	<ScrollTop />
</template>
<script setup lang="ts">
import { ref, onMounted } from "vue";
import MaintenanceService from "@/services/maintenance-service";
import Toolbar from "primevue/toolbar";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import ProgressBar from "primevue/progressbar";
import Button from "primevue/button";
import AlbumService from "@/services/album-service";
import { AugmentedAlbum, useTreeOperations } from "@/composables/album/treeOperations";
import { useToast } from "primevue/usetoast";
import FixTreeLine from "@/components/maintenance/FixTreeLine.vue";
import Left from "@/components/maintenance/mini/Left.vue";
import Right from "@/components/maintenance/mini/Right.vue";
import LeftWarn from "@/components/maintenance/mini/LeftWarn.vue";
import RightWarn from "@/components/maintenance/mini/RightWarn.vue";
import ScrollTop from "primevue/scrolltop";
import VirtualScroller from "primevue/virtualscroller";

const albums = ref<AugmentedAlbum[] | undefined>(undefined);
const originalAlbums = ref<App.Http.Resources.Diagnostics.AlbumTree[] | undefined>(undefined);
const hoverId = ref<string | undefined>(undefined);
const toast = useToast();
const albumIds = ref<string[]>([]);

const { isValidated, validate, prepareAlbums, check, incrementLft, incrementRgt, decrementLft, decrementRgt } = useTreeOperations(
	originalAlbums,
	albums,
);

function fetch() {
	albums.value = undefined;
	MaintenanceService.fullTreeGet().then((data) => {
		originalAlbums.value = data.data;
		albumIds.value = originalAlbums.value.map((a) => a.id);
		prepareAlbums();
	});
}

function setHoverId(id: string) {
	hoverId.value = id;
}

function apply() {
	if (albums.value === undefined) {
		return;
	}

	if (!validate()) {
		toast.add({ severity: "error", summary: "Invalid tree!", detail: "We are not applying this as this is guaranteed to be a broken state." });
		return;
	}

	const data = albums.value.map((a) => {
		return {
			id: a.id,
			_lft: a._lft,
			_rgt: a._rgt,
			parent_id: a.parent_id,
		};
	});

	MaintenanceService.updateFullTree(data).then(() => {
		AlbumService.clearCache();
		fetch();
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
