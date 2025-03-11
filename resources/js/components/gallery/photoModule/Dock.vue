<template>
	<div
		:class="{
			'absolute top-0 sm:h-1/4 w-full sm:w-1/2 left-1/2 -translate-x-1/2': true,
			'opacity-50 lg:opacity-10 group lg:hover:opacity-100 transition-opacity duration-500 ease-in-out': true,
			'z-20 mt-14 sm:mt-0': true,
			hidden: is_slideshow_active,
			'hidden sm:block': are_details_open,
		}"
	>
		<span class="absolute left-1/2 -translate-x-1/2 p-1 min-w-[25%] w-full filter-shadow text-center">
			<DockButton
				icon="star"
				:class="props.photo.is_starred ? 'fill-yellow-500 lg:hover:fill-yellow-100' : 'fill-white lg:hover:fill-yellow-500'"
				v-tooltip.bottom="props.photo.is_starred ? $t('gallery.photo.actions.unstar') : $t('gallery.photo.actions.star')"
				v-on:click="emits('toggleStar')"
			/>
			<DockButton
				pi="image"
				class="lg:hover:text-primary-500 text-white"
				v-tooltip.bottom="$t('gallery.photo.actions.set_album_header')"
				v-on:click="emits('setAlbumHeader')"
			/>
			<template v-if="lycheeStore.can_rotate">
				<DockButton icon="counterclockwise" class="fill-white lg:hover:fill-primary-500" v-on:click="emits('rotatePhotoCCW')" />
				<DockButton icon="clockwise" class="fill-white lg:hover:fill-primary-500" v-on:click="emits('rotatePhotoCW')" />
			</template>
			<DockButton
				icon="transfer"
				class="fill-white lg:hover:fill-primary-500"
				v-tooltip.bottom="$t('gallery.photo.actions.move')"
				v-on:click="emits('toggleMove')"
			/>
			<DockButton
				icon="trash"
				class="fill-red-600 lg:fill-white lg:hover:fill-red-600"
				v-tooltip.bottom="$t('gallery.photo.actions.delete')"
				v-on:click="emits('toggleDelete')"
			/>
		</span>
	</div>
</template>
<script setup lang="ts">
import DockButton from "./DockButton.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";

const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const { is_slideshow_active, are_details_open } = storeToRefs(togglableStore);

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource;
}>();

const emits = defineEmits<{
	toggleStar: [];
	setAlbumHeader: [];
	rotatePhotoCCW: [];
	rotatePhotoCW: [];
	toggleMove: [];
	toggleDelete: [];
}>();
</script>
