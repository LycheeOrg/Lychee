<template>
	<Collapse class="w-full flex justify-center flex-wrap flex-row-reverse" :when="are_details_open">
		<ul
			v-if="props.config.is_base_album"
			class="sm:mt-7 sm:px-7 mb-4 text-sm w-full xl:w-1/6 xl:px-9 max-xl:w-full max-xl:flex max-xl:justify-center"
		>
			<li
				class="px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none xl:border-l-2 max-xl:border-b-2 border-solid border-primary-500 text-primary-500 hover:border-primary-300 hover:text-primary-300"
				:class="activeTab === 0 ? 'bg-primary-500/5' : ''"
				v-on:click="activeTab = 0"
			>
				{{ $t("gallery.album.tabs.about") }}
			</li>
			<li
				v-if="canShare"
				class="px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none xl:border-l-2 max-xl:border-b-2 border-solid border-primary-500 text-primary-500 hover:border-primary-300 hover:text-primary-300"
				:class="activeTab === 1 ? 'bg-primary-500/5' : ''"
				v-on:click="activeTab = 1"
			>
				{{ $t("gallery.album.tabs.share") }}
			</li>
			<li
				v-if="canMove"
				class="px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none xl:border-l-2 max-xl:border-b-2 border-solid border-primary-500 text-primary-500 hover:border-primary-300 hover:text-primary-300"
				:class="activeTab === 2 ? 'bg-primary-500/5' : ''"
				v-on:click="activeTab = 2"
			>
				{{ $t("gallery.album.tabs.move") }}
			</li>
			<li
				v-if="canDelete || canTransfer"
				class="px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none xl:border-l-2 max-xl:border-b-2 border-solid text-red-700 border-red-700 hover:border-red-600 hover:text-red-600"
				:class="activeTab === 3 ? 'bg-red-700/10' : ''"
				v-on:click="activeTab = 3"
			>
				{{ $t("gallery.album.tabs.danger") }}
			</li>
		</ul>
		<div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" v-if="activeTab === 0">
			<!-- @vue-expect-error editable exist in that case. -->
			<AlbumProperties
				v-if="props.config.is_base_album"
				:editable="props.album.editable"
				:photos="props.album.photos"
				:key="'properties_' + props.album.id"
			/>
			<AlbumVisibility :album="props.album" :config="props.config" :key="'visibility_' + props.album.id" />
		</div>
		<!-- @if($this->flags->is_base_album)  -->
		<div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" v-if="activeTab === 1 && canShare">
			<!-- @vue-expect-error -->
			<AlbumShare :album="props.album" :key="'share_' + props.album.id" />
		</div>
		<div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" v-if="activeTab === 2 && canMove">
			<!-- @vue-expect-error -->
			<AlbumMove :album="props.album" :key="'move_' + props.album.id" />
		</div>
		<div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" v-if="activeTab === 3 && (canDelete || canTransfer)">
			<!-- @vue-expect-error -->
			<AlbumTransfer v-if="canTransfer" :album="props.album" :key="'transfer_' + props.album.id" />
			<AlbumDelete
				v-if="canDelete"
				:album="props.album"
				:is_model_album="props.config.is_model_album"
				:key="'delete_' + props.album.id"
				@deleted="close"
			/>
		</div>
	</Collapse>
</template>
<script setup lang="ts">
import { Collapse } from "vue-collapsed";
import { computed, ref } from "vue";
import UsersService from "@/services/users-service";
import AlbumProperties from "@/components/forms/album/AlbumProperties.vue";
import AlbumVisibility from "@/components/forms/album/AlbumVisibility.vue";
import AlbumDelete from "@/components/forms/album/AlbumDelete.vue";
import AlbumMove from "../forms/album/AlbumMove.vue";
import AlbumTransfer from "../forms/album/AlbumTransfer.vue";
import AlbumShare from "../forms/album/AlbumShare.vue";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.SmartAlbumResource | App.Http.Resources.Models.TagAlbumResource;
	config: App.Http.Resources.GalleryConfigs.AlbumConfig;
}>();

const togglableStore = useTogglablesStateStore();
const { are_details_open } = storeToRefs(togglableStore);

const activeTab = ref(0);
const numUsers = ref(0);

UsersService.count().then((data) => {
	numUsers.value = data.data;
});

const canShare = computed(() => props.album.rights.can_share_with_users && numUsers.value > 1 && props.config.is_base_album);
const canMove = computed(() => props.config.is_model_album && props.album.rights.can_move);
const canTransfer = computed(() => props.config.is_base_album && numUsers.value > 1 && props.album.rights.can_transfer);
const canDelete = computed(() => props.config.is_base_album && props.album.rights.can_delete);

function close() {
	activeTab.value = 0;
	are_details_open.value = false;
}
</script>
