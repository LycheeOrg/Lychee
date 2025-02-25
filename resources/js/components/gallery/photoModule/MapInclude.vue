<template>
	<div :data-layer="map_provider?.layer" :data-provider="map_provider?.attribution" id="leaflet_map_single_photo" :class="classVal"></div>
</template>
<script setup lang="ts">
import { useSidebarMap } from "@/services/sidebar-map";
import { computed, onMounted, watch } from "vue";

const props = defineProps<{
	latitude: number | null;
	longitude: number | null;
}>();
const { latitude, longitude, map_provider, load, onMount } = useSidebarMap(props.latitude, props.longitude);

onMounted(() => {
	onMount();
});

const classVal = computed(() => {
	return {
		"col-span-2": true,
		"bg-red-500": true,
		"h-48": true,
		"my-0.5": true,
		"mx-3": true,
		hidden: !latitude.value && !longitude.value,
	};
});

watch(
	() => [props.latitude, props.longitude],
	([newlatitude, newlongitude], [_oldlatitude, _oldlongitude]) => {
		latitude.value = newlatitude;
		longitude.value = newlongitude;
		load();
	},
);
</script>
