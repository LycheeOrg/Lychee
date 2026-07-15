<template>
	<LoadingProgress v-model:loading="isLoading" />
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("fix-tree.title") }}
	</UHeader>
	<div class="text-muted">
		<div class="text-center mt-2 p-2">
			<span v-html="$t('fix-tree.intro')" /><br />
			<span class="text-error text-lg font-bold"
				><UIcon name="lucide:triangle-alert" class="ltr:mr-2 rtl:ml-2" />{{ $t("fix-tree.warning") }}</span
			>
		</div>
		<div class="md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto">
			<h2 class="text-highlighted text-lg font-bold">
				<UIcon name="lucide:circle-help" class="ltr:mr-2 rtl:ml-2" />{{ $t("fix-tree.help.header") }}
			</h2>
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
				<li><UIcon name="lucide:triangle-alert" class="ltr:mr-2 rtl:ml-2 text-orange-500" />{{ $t("fix-tree.help.slowness") }}</li>
			</ul>
		</div>

		<div
			v-if="albums !== undefined"
			class="md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto sticky z-30 w-full top-0 flex h-11 justify-center"
		>
			<UButton variant="ghost" color="neutral" class="px-8 font-bold" @click="fetch">{{ $t("fix-tree.buttons.reset") }}</UButton>
			<UButton color="warning" class="px-8 font-bold" @click="check">{{ $t("fix-tree.buttons.check") }}</UButton>
			<UButton color="error" class="px-8 font-bold" @click="apply">
				<UIcon v-if="!isValidated" name="lucide:triangle-alert" />{{ $t("fix-tree.buttons.apply") }}
			</UButton>
		</div>
		<div
			v-if="albums !== undefined"
			class="mt-16 pb-2 flex justify-between md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mx-auto hover:bg-primary/5 gap-8 items-center font-bold text-lg text-highlighted border-b border-b-white/50"
		>
			<div class="w-1/2">{{ $t("fix-tree.table.title") }}</div>
			<div class="flex w-1/4 gap-4">
				<div class="w-full ltr:pl-4 rtl:pr-4">{{ $t("fix-tree.table.left") }}</div>
				<div class="w-full ltr:pl-4 rtl:pr-4">{{ $t("fix-tree.table.right") }}</div>
			</div>
			<div class="flex w-1/4 justify-between">
				<div class="w-full">{{ $t("fix-tree.table.id") }}</div>
				<div class="w-full ltr:text-right rtl:text-left">{{ $t("fix-tree.table.parent") }}</div>
			</div>
		</div>
		<UScrollArea :items="albums ?? []" :virtualize="{ estimateSize: 50 }" class="h-screen">
			<template #default="{ item }">
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
		</UScrollArea>
	</div>
	<ScrollTop />
</template>
<script setup lang="ts">
import { ref, onMounted } from "vue";
import MaintenanceService from "@/services/maintenance-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import AlbumService from "@/services/album-service";
import { AugmentedAlbum, useTreeOperations } from "@/composables/album/treeOperations";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import FixTreeLine from "@/v8/components/maintenance/FixTreeLine.vue";
import LoadingProgress from "@/v8/components/loading/LoadingProgress.vue";
import ScrollTop from "@/v8/components/ScrollTop.vue";
import { sprintf } from "sprintf-js";
import { trans } from "laravel-vue-i18n";

const albums = ref<AugmentedAlbum[] | undefined>(undefined);
const originalAlbums = ref<App.Http.Resources.Diagnostics.AlbumTree[] | undefined>(undefined);
const hoverId = ref<string | undefined>(undefined);
const toast = useAppToast();
const albumIds = ref<string[]>([]);
const isLoading = ref(true);

const { isValidated, validate, prepareAlbums, check, incrementLft, incrementRgt, decrementLft, decrementRgt, getModifiedAlbums } = useTreeOperations(
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

	const data = getModifiedAlbums();

	if (data.length === 0) {
		toast.add({
			severity: "info",
			summary: trans("fix-tree.no-changes"),
			life: 3000,
		});
		return;
	}

	MaintenanceService.updateFullTree(data).then(() => {
		AlbumService.clearCache();
		fetch();
	});
}

onMounted(() => {
	fetch();
});
</script>
