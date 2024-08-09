<template>
	<Collapse class="w-full flex justify-center flex-wrap flex-row-reverse" :when="detailsOpen">
		<ul
			v-if="props.config.is_base_album"
			class="sm:mt-7 sm:px-7 mb-4 text-sm w-full xl:w-1/6 xl:px-9 max-xl:w-full max-xl:flex max-xl:justify-center"
		>
			<li
				class="px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none xl:border-l-2 max-xl:border-b-2 border-solid border-primary-500 text-primary-500 hover:border-primary-300 hover:text-primary-300"
				:class="activeTab === 0 ? 'bg-primary-500/5' : ''"
				v-on:click="activeTab = 0"
			>
				{{ $t("lychee.ABOUT_ALBUM") }}
			</li>
			<li
				v-if="album.rights.can_share_with_users && numUsers > 1"
				class="px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none xl:border-l-2 max-xl:border-b-2 border-solid border-primary-500 text-primary-500 hover:border-primary-300 hover:text-primary-300"
				:class="activeTab === 1 ? 'bg-primary-500/5' : ''"
				v-on:click="activeTab = 1"
			>
				{{ $t("lychee.SHARE_ALBUM") }}
			</li>
			<li
				v-if="album.rights.can_move"
				class="px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none xl:border-l-2 max-xl:border-b-2 border-solid border-primary-500 text-primary-500 hover:border-primary-300 hover:text-primary-300"
				:class="activeTab === 2 ? 'bg-primary-500/5' : ''"
				v-on:click="activeTab = 2"
			>
				{{ $t("lychee.MOVE_ALBUM") }}
			</li>
			<li
				v-if="album.rights.can_delete"
				class="px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none xl:border-l-2 max-xl:border-b-2 border-solid text-red-700 border-red-700 hover:border-red-600 hover:text-red-600"
				:class="activeTab === 3 ? 'bg-red-700/10' : ''"
				v-on:click="activeTab = 3"
			>
				{{ "DANGER ZONE" }}
			</li>
		</ul>
		<div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" v-if="activeTab === 0">
			<!-- @vue-expect-error editable exist in that case. -->
			<AlbumProperties v-if="props.config.is_base_album" :editable="props.album.editable" />
			<AlbumVisibility :album="props.album" :config="props.config" />
		</div>
		<!-- @if($this->flags->is_base_album)  -->
		<!-- @if ($this->rights->can_share_with_users === true && $this->num_users > 1) -->
		<div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" v-if="activeTab === 1">
			<!-- <livewire:forms.album.share-with :album="$this->album" /> -->
		</div>
		<!-- @endif
    @if($this->rights->can_move === true) -->
		<div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" v-if="activeTab === 2">
			<!-- <livewire:forms.album.move-panel :album="$this->album" /> -->
		</div>
		<!-- @endif
    @if($this->rights->can_delete === true) -->
		<div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" v-if="activeTab === 3">
			<!-- {{-- We only display this menu if there are more than 1 user, it does not make sense otherwise --}}
            @if ($this->num_users > 1) -->
			<!-- <livewire:forms.album.transfer :album="$this->album"  lazy /> -->
			<!-- @endif -->
			<!-- <livewire:forms.album.delete-panel :album="$this->album" /> -->
		</div>
		<!-- @endif
    @endif -->
	</Collapse>
</template>
<script setup lang="ts">
import { Collapse } from "vue-collapsed";
import { ref } from "vue";
import UsersService from "@/services/users-service";
import AlbumProperties from "@/components/forms/album/AlbumProperties.vue";
import AlbumVisibility from "../forms/album/AlbumVisibility.vue";

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.SmartAlbumResource | App.Http.Resources.Models.TagAlbumResource;
	config: App.Http.Resources.GalleryConfigs.AlbumConfig;
}>();
const detailsOpen = defineModel<boolean>({ required: true });
const activeTab = ref(0);
const numUsers = ref(1);

UsersService.count().then((data) => {
	numUsers.value = data.data;
});
</script>
