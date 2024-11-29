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
	<div class="text-muted-color text-center mt-2 p-2">
		This page allows you to re-order your albums manually.<br />
		<span class="text-danger-700 text-lg font-bold"
			><i class="pi text-danger-700 pi-exclamation-triangle mr-2" />You can really break installation here, modify at your own risks.</span
		>
	</div>

	<div class="md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto w-full flex flex-col text-muted-color">
		<div class="sticky z-30 w-full top-0 flex h-11 justify-center" v-if="albums !== undefined">
			<Button text severity="secondary" class="border-none px-8 font-bold" @click="fetch">Reset</Button>
			<Button severity="warn" class="border-none px-8 font-bold" @click="check">Check</Button>
			<Button severity="danger" class="border-none px-8 font-bold" @click="apply">Apply</Button>
		</div>
		<FixTreeLine
			v-for="album in albums"
			v-model:lft="album._lft"
			v-model:rgt="album._rgt"
			:album="album"
			:is-hover-id="hoverId === album.trimmedId"
			:is-hover-parent="hoverId === album.trimmedParentId"
			@hover-id="setHoverId"
			@decrement-lft="decrementLft(album.id)"
			@increment-lft="incrementLft(album.id)"
			@decrement-rgt="decrementRgt(album.id)"
			@increment-rgt="incrementRgt(album.id)"
		/>
	</div>
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

const albums = ref<AugmentedAlbum[] | undefined>(undefined);
const originalAlbums = ref<App.Http.Resources.Diagnostics.AlbumTree[] | undefined>(undefined);
const hoverId = ref<string | undefined>(undefined);
const toast = useToast();

const { validate, prepareAlbums, check, incrementLft, incrementRgt, decrementLft, decrementRgt } = useTreeOperations(originalAlbums, albums);

function fetch() {
	albums.value = undefined;
	MaintenanceService.fullTreeGet().then((data) => {
		originalAlbums.value = data.data;
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
