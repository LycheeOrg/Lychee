<template>
	<LoadingProgress v-model:loading="isLoading" />
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ $t("fix-tree.title") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<div class="text-muted-color">
		<div class="text-center mt-2 p-2">
			<span v-html="$t('fix-tree.intro')" /><br />
			<span class="text-danger-700 text-lg font-bold"
				><i class="pi text-danger-700 pi-exclamation-triangle mr-2" />{{ $t("fix-tree.warning") }}</span
			>
		</div>
		<div class="md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto">
			<h2 class="text-muted-color-emphasis text-lg font-bold"><i class="pi pi-question-circle mr-2" />{{ $t("fix-tree.help.header") }}</h2>
			<ul class="list-disc list-inside">
				<li v-html="$t('fix-tree.help.hover')" />
				<li v-html="sprintf($t('fix-tree.help.convenience'), $t('fix-tree.help.left'), $t('fix-tree.help.right'))" />
				<li v-html="sprintf($t('fix-tree.help.left-right-warn'), $t('fix-tree.help.left'), $t('fix-tree.help.right'))" />
				<li
					v-html="
						sprintf(
							$t('fix-tree.help.parent-marked'),
							$t('fix-tree.help.left'),
							$t('fix-tree.help.right'),
							$t('fix-tree.help.left'),
							$t('fix-tree.help.right'),
						)
					"
				/>
				<li><i class="pi pi-exclamation-triangle mr-2 text-orange-500" />{{ $t("fix-tree.help.slowness") }}</li>
			</ul>
		</div>

		<div
			class="md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto sticky z-30 w-full top-0 flex h-11 justify-center"
			v-if="albums !== undefined"
		>
			<Button text severity="secondary" class="border-none px-8 font-bold" @click="fetch">{{ $t("fix-tree.buttons.reset") }}</Button>
			<Button severity="warn" class="border-none px-8 font-bold" @click="check">{{ $t("fix-tree.buttons.check") }}</Button>
			<Button severity="danger" class="border-none px-8 font-bold" @click="apply">
				<i class="pi pi-exclamation-triangle" v-if="!isValidated" />{{ $t("fix-tree.buttons.apply") }}
			</Button>
		</div>
		<VirtualScroller :items="albums" :itemSize="50" class="h-screen">
			<template v-slot:item="{ item, options }">
				<template v-if="options.first">
					<div
						class="mt-16 pb-2 flex justify-between md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mx-auto hover:bg-primary-emphasis/5 gap-8 items-center font-bold text-lg text-color-emphasis border-b border-b-white/50"
					>
						<div class="w-1/2">{{ $t("fix-tree.table.title") }}</div>
						<div class="flex w-1/4 gap-4">
							<div class="w-full pl-4">{{ $t("fix-tree.table.left") }}</div>
							<div class="w-full pl-4">{{ $t("fix-tree.table.right") }}</div>
						</div>
						<div class="flex w-1/4 justify-between">
							<div class="w-full">{{ $t("fix-tree.table.id") }}</div>
							<div class="w-full text-right">{{ $t("fix-tree.table.parent") }}</div>
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
import Button from "primevue/button";
import ScrollTop from "primevue/scrolltop";
import VirtualScroller from "primevue/virtualscroller";
import { useToast } from "primevue/usetoast";
import AlbumService from "@/services/album-service";
import { AugmentedAlbum, useTreeOperations } from "@/composables/album/treeOperations";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import FixTreeLine from "@/components/maintenance/FixTreeLine.vue";
import LoadingProgress from "@/components/gallery/LoadingProgress.vue";
import { sprintf } from "sprintf-js";
import { trans } from "laravel-vue-i18n";

const albums = ref<AugmentedAlbum[] | undefined>(undefined);
const originalAlbums = ref<App.Http.Resources.Diagnostics.AlbumTree[] | undefined>(undefined);
const hoverId = ref<string | undefined>(undefined);
const toast = useToast();
const albumIds = ref<string[]>([]);
const isLoading = ref(true);

const { isValidated, validate, prepareAlbums, check, incrementLft, incrementRgt, decrementLft, decrementRgt } = useTreeOperations(
	originalAlbums,
	albums,
	toast,
);

function fetch() {
	albums.value = undefined;
	isLoading.value = true;
	MaintenanceService.fullTreeGet().then((data) => {
		originalAlbums.value = data.data;
		albumIds.value = originalAlbums.value.map((a) => a.id);
		isLoading.value = false;
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
		toast.add({
			severity: "error",
			summary: trans("fix-tree.errors.invalid"),
			detail: trans("fix-tree.errors.invalid_details"),
			life: 3000,
		});
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
