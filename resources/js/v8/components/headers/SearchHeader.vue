<template>
	<div class="relative w-full border-0 h-14 flex items-center justify-between px-2">
		<GoBack @go-back="emits('goBack')" />
		<span class="sm:hidden font-bold">
			{{ $t("gallery.search.title") }}
		</span>
		<span class="hidden sm:block font-bold text-sm lg:text-base text-center w-full absolute left-1/2 -translate-x-1/2 pointer-events-none">{{
			props.title
		}}</span>
		<div></div>
	</div>
</template>
<script setup lang="ts">
import GoBack from "./GoBack.vue";

const props = defineProps<{
	title: string;
}>();

const emits = defineEmits<{
	goBack: [];
}>();
</script>
