<template>
	<Collapse class="w-full flex justify-center flex-wrap flex-row-reverse" :when="is_album_edit_open">
		<ul
			v-if="albumStore.config?.is_base_album"
			class="sm:mt-7 sm:px-7 mb-4 text-sm w-full xl:w-1/6 xl:px-9 max-xl:w-full max-xl:flex max-xl:justify-center"
		>
			<li
				v-if="hasAboutTab"
				:class="{
					'px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none': true,
					'xl:border-l-2': isLTR(),
					'xl:border-r-2': !isLTR(),
					'max-xl:border-b-2 border-solid border-primary-500 text-primary-500 hover:border-primary-300 hover:text-primary-300': true,
					'bg-primary-500/5': activeTab === 0,
				}"
				@click="activeTab = 0"
			>
				{{ $t("gallery.album.tabs.about") }}
			</li>
			<li
				v-if="canShare"
				:class="{
					'px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none': true,
					'xl:border-l-2': isLTR(),
					'xl:border-r-2': !isLTR(),
					'max-xl:border-b-2 border-solid border-primary-500 text-primary-500 hover:border-primary-300 hover:text-primary-300': true,
					'bg-primary-500/5': activeTab === 1,
				}"
				@click="activeTab = 1"
			>
				{{ $t("gallery.album.tabs.share") }}
			</li>
			<li
				v-if="canMove"
				:class="{
					'px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none': true,
					'xl:border-l-2': isLTR(),
					'xl:border-r-2': !isLTR(),
					'max-xl:border-b-2 border-solid border-primary-500 text-primary-500 hover:border-primary-300 hover:text-primary-300': true,
					'bg-primary-500/5': activeTab === 2,
				}"
				@click="activeTab = 2"
			>
				{{ $t("gallery.album.tabs.move") }}
			</li>
			<li
				v-if="canManagePurchase"
				:class="{
					'px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none': true,
					'xl:border-l-2': isLTR(),
					'xl:border-r-2': !isLTR(),
					'max-xl:border-b-2 border-solid border-primary-500 text-primary-500 hover:border-primary-300 hover:text-primary-300': true,
					'bg-primary-500/5': activeTab === 4,
				}"
				@click="activeTab = 4"
			>
				{{ $t("gallery.album.tabs.shop") }}
			</li>
			<li
				v-if="canDelete || canTransfer"
				:class="{
					'px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none': true,
					'xl:border-l-2': isLTR(),
					'xl:border-r-2': !isLTR(),
					'max-xl:border-b-2 border-solid text-red-700 border-red-700 hover:border-red-600 hover:text-red-600': true,
					'bg-red-700/10': activeTab === 3,
				}"
				@click="activeTab = 3"
			>
				{{ $t("gallery.album.tabs.danger") }}
			</li>
		</ul>
		<div v-if="activeTab === 0" class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 ltr:pl-7 rtl:pr-7">
			<AlbumProperties v-if="canEditProperties" :key="`properties_${albumStore.album?.id}`" />
			<AlbumVisibility v-if="canChangeVisibility" :key="`visibility_${albumStore.album?.id}`" />
		</div>
		<!-- @if($this->flags->is_base_album)  -->
		<div v-if="activeTab === 1 && canShare" class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 ltr:pl-7 rtl:pr-7">
			<AlbumShare :key="`share_${albumStore.album?.id}`" />
		</div>
		<div v-if="activeTab === 2 && canMove" class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 ltr:pl-7 rtl:pr-7">
			<AlbumMove :key="`move_${albumStore.album?.id}`" />
		</div>
		<div v-if="activeTab === 4 && canManagePurchase" class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 ltr:pl-7 rtl:pr-7">
			<AlbumPurchasable :key="`purchasable_${albumStore.album?.id}`" />
		</div>
		<div
			v-if="activeTab === 3 && (canDelete || canTransfer)"
			class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 ltr:pl-7 rtl:pr-7"
		>
			<AlbumTransfer v-if="canTransfer" :key="`transfer_${albumStore.album?.id}`" />
			<AlbumDelete v-if="canDelete" :key="`delete_${albumStore.album?.id}`" @deleted="close" />
		</div>
	</Collapse>
</template>
<script setup lang="ts">
import { Collapse } from "vue-collapsed";
import { computed, ref } from "vue";
import UsersService from "@/services/users-service";
import AlbumProperties from "@/v7/components/forms/album/AlbumProperties.vue";
import AlbumVisibility from "@/v7/components/forms/album/AlbumVisibility.vue";
import AlbumDelete from "@/v7/components/forms/album/AlbumDelete.vue";
import AlbumMove from "@/v7/components/forms/album/AlbumMove.vue";
import AlbumPurchasable from "@/v7/components/forms/album/AlbumPurchasable.vue";
import AlbumTransfer from "@/v7/components/forms/album/AlbumTransfer.vue";
import AlbumShare from "@/v7/components/forms/album/AlbumShare.vue";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useLtRorRtL } from "@/utils/Helpers";
import { useAlbumStore } from "@/stores/AlbumState";

const { isLTR } = useLtRorRtL();

const albumStore = useAlbumStore();
const togglableStore = useTogglablesStateStore();
const { is_album_edit_open } = storeToRefs(togglableStore);

const numUsers = ref(0);

UsersService.count().then((data) => {
	numUsers.value = data.data;
});

const canShare = computed(() => albumStore.rights?.can_share_with_users && numUsers.value > 1 && albumStore.config?.is_base_album);
const canMove = computed(() => albumStore.config?.is_model_album && albumStore.rights?.can_move);
const canTransfer = computed(() => albumStore.config?.is_base_album && numUsers.value > 1 && albumStore.rights?.can_transfer);
const canDelete = computed(() => albumStore.config?.is_base_album && albumStore.rights?.can_delete);
const canManagePurchase = computed(() => albumStore.config?.is_model_album && albumStore.rights?.can_make_purchasable);
// An owner with the share right but no edit right sees visibility, sharing and transfer only (Feature 118, FR-118-06).
const canEditProperties = computed(() => albumStore.config?.is_base_album === true && albumStore.rights?.can_edit === true);
// The protection policy is a sharing decision: the same rule as transfer.
const canChangeVisibility = computed(() => albumStore.rights?.can_transfer ?? false);
const hasAboutTab = computed(() => canEditProperties.value || canChangeVisibility.value);

// First tab the user may see: the "About" tab is gone for users who may neither edit nor share.
function firstTab(): number {
	if (hasAboutTab.value) {
		return 0;
	}
	if (canShare.value) {
		return 1;
	}
	if (canMove.value) {
		return 2;
	}
	return canManagePurchase.value ? 4 : 3;
}

const activeTab = ref(firstTab());

function close() {
	activeTab.value = firstTab();
	is_album_edit_open.value = false;
}
</script>
