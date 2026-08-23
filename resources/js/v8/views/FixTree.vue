<template>
	<LoadingProgress v-model:loading="isLoading" />
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("fix-tree.title") }}
	</UHeader>
	<UMain class="text-muted">
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
			class="md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto sticky z-30 w-full top-(--ui-header-height) flex h-11 justify-center"
		>
			<UButton variant="ghost" color="neutral" class="px-8 font-bold" @click="fetch">{{ $t("fix-tree.buttons.reset") }}</UButton>
			<UButton variant="solid" color="warning" class="px-8 font-bold" @click="check">{{ $t("fix-tree.buttons.check") }}</UButton>
			<UButton variant="solid" color="error" class="px-8 font-bold" @click="apply">
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
		<UScrollArea
			v-if="!isLoading"
			ref="scrollArea"
			v-slot="{ item, index }"
			:items="albumIds"
			:virtualize="{
				estimateSize: 32,
				skipMeasurement: true,
			}"
			class="h-[calc(100vh-var(--ui-header-height))]"
		>
			<div
				:key="item"
				class="flex justify-between hover:bg-primary/5 gap-8 items-center md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mx-auto"
				:class="{
					'text-error! font-bold': albums!.isDuplicate_lft[index] || albums!.isDuplicate_rgt[index] || !albums!.isExpectedParentId[index],
				}"
			>
				<div class="w-1/2">
					<span v-if="(albums?.prefix[index].length ?? 0) > 4" class="font-mono" v-html="albums!.prefix[index].slice(0, -2)" />
					<span v-if="(albums?.prefix[index].length ?? 0) > 0" class="ltr:mr-2 rtl:ml-2">
						{{ isLTR() ? "└ " : "┘" }}
					</span>
					<span>
						{{ albums!.title[index] }}
					</span>
				</div>
				<div class="flex w-1/4 gap-4">
					<div class="flex">
						<UInput
							class="w-full px-2"
							v-model="albums!._lft[index]"
							:color="albums!._lft[index] === 0 || albums!.isDuplicate_lft[index] ? 'error' : undefined"
							:step="1"
							placeholder="_lft"
						/>
						<UButton variant="ghost" color="neutral" icon="lucide:chevron-up" class="py-0.5" @click="incrementLft(albums!.id[index])" />
						<UButton variant="ghost" color="neutral" icon="lucide:chevron-down" class="py-0.5" @click="decrementLft(albums!.id[index])" />
					</div>
					<div class="flex">
						<UInput
							class="w-full px-2"
							v-model="albums!._rgt[index]"
							:color="albums!._rgt[index] === 0 || albums!.isDuplicate_rgt[index] ? 'error' : undefined"
							:step="1"
							placeholder="_rgt"
						/>
						<UButton variant="ghost" color="neutral" icon="lucide:chevron-up" class="py-0.5" @click="incrementRgt(albums!.id[index])" />
						<UButton variant="ghost" color="neutral" icon="lucide:chevron-down" class="py-0.5" @click="decrementRgt(albums!.id[index])" />
					</div>
				</div>
				<div class="flex w-1/4 justify-between items-center">
					<div>
						{{ albums!.trimmedId[index] }}
						<LeftWarn v-if="albums!.isDuplicate_lft[index]" class="ltr:ml-2 rtl:mr-2" />
						<RightWarn v-if="albums!.isDuplicate_rgt[index]" class="ltr:ml-2 rtl:mr-2" />
					</div>
					<div class="cursor-pointer" @click="editingIndex = index">
						<span
							:class="{
								'text-highlighted': albums!.trimmedParentId[index] === 'root',
								'text-error! font-bold': !albums!.isExpectedParentId[index],
							}"
							>{{ (albums!.parent_id[index] ?? "root").slice(0, 6) }}</span
						>
					</div>
				</div>
			</div>
		</UScrollArea>
		<UModal v-model:open="isParentModalOpen" :dismissible="true">
			<template #header>
				<span class="font-bold text-xl">{{ $t("fix-tree.table.parent") }}</span>
			</template>
			<template #body>
				<USelectMenu
					:model-value="editingIndex !== undefined ? (albums?.parent_id[editingIndex] ?? undefined) : undefined"
					class="w-full"
					searchable
					:virtualize="{
						estimateSize: 32,
					}"
					:items="albumIds"
					@update:model-value="(v: string | undefined) => updateParentId(v)"
				>
					<template #item-label="{ item }">{{ (item as string).slice(0, 6) }}</template>
					<template #default="{ modelValue }">
						<span v-if="modelValue">{{ (modelValue as string).slice(0, 6) }}</span>
						<span v-else>root</span>
					</template>
				</USelectMenu>
			</template>
		</UModal>
	</UMain>
	<ScrollTop />
</template>
<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import MaintenanceService, { type UpdateTreeData } from "@/services/maintenance-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import AlbumService from "@/services/album-service";
import AlbumListV3Service from "@/services/album-list-v3-service";
import { useAlbumListStore } from "@/stores/AlbumListState";
import { type AlbumTree, type AugmentedAlbumTree, useTreeOperations } from "@/v8/composables/album/treeOperations";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import LeftWarn from "@/v8/components/maintenance/mini/LeftWarn.vue";
import RightWarn from "@/v8/components/maintenance/mini/RightWarn.vue";
import LoadingProgress from "@/v8/components/loading/LoadingProgress.vue";
import ScrollTop from "@/v8/components/ScrollTop.vue";
import { sprintf } from "sprintf-js";
import { trans } from "laravel-vue-i18n";
import { useLtRorRtL } from "@/utils/Helpers";

const { isLTR } = useLtRorRtL();

const albums = ref<AugmentedAlbumTree | undefined>(undefined);
const originalAlbums = ref<AlbumTree | undefined>(undefined);
const editingIndex = ref<number | undefined>(undefined);
const isParentModalOpen = computed({
	get: () => editingIndex.value !== undefined,
	set: (open: boolean) => {
		if (!open) {
			editingIndex.value = undefined;
		}
	},
});

const toast = useAppToast();
const albumIds = ref<string[]>([]);
const isLoading = ref(true);

const albumListStore = useAlbumListStore();

const { isValidated, validate, prepareAlbums, check, incrementLft, incrementRgt, decrementLft, decrementRgt, getModifiedAlbums } = useTreeOperations(
	originalAlbums,
	albums,
	toast,
);

function adaptAlbumListToTree(data: App.Http.Resources.V3.AlbumListResource): AlbumTree {
	return {
		id: data.ids,
		title: data.titles,
		parent_id: data.parent_ids!,
		_lft: Int32Array.from(data._lft),
		_rgt: Int32Array.from(data._rgt),
	};
}

function fetch() {
	albums.value = undefined;
	isLoading.value = true;

	AlbumListV3Service.getAlbums({ with_parent_id: true }).then((response) => {
		originalAlbums.value = adaptAlbumListToTree(response.data);
		albumIds.value = originalAlbums.value.id;
		void prepareAlbums().finally(() => {
			isLoading.value = false;
		});
	});
}

function updateParentId(val: string | undefined) {
	if (albums.value === undefined || editingIndex.value === undefined) {
		return;
	}
	// guarantee we never have undefined.
	albums.value.parent_id[editingIndex.value] = val ?? null;
	editingIndex.value = undefined;
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

	const modified = getModifiedAlbums();

	if (modified.id.length === 0) {
		toast.add({
			severity: "info",
			summary: trans("fix-tree.no-changes"),
			life: 3000,
		});
		return;
	}

	// `Maintenance::fullTree` still takes one row per album; struct-of-arrays -> array-of-structs
	// only happens here, at the wire boundary.
	const data: UpdateTreeData[] = modified.id.map((id, i) => ({
		id,
		_lft: modified._lft[i],
		_rgt: modified._rgt[i],
		parent_id: modified.parent_id[i],
	}));

	MaintenanceService.updateFullTree(data).then(() => {
		AlbumService.clearCache();
		albumListStore.invalidate();
		fetch();
	});
}

onMounted(() => {
	fetch();
});
</script>
