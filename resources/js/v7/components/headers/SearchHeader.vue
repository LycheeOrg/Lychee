<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<GoBack @go-back="emits('goBack')" />
		</template>
		<template #center>
			<span class="sm:hidden font-bold">
				{{ $t("gallery.search.title") }}
			</span>
			<span class="hidden sm:block font-bold text-sm lg:text-base text-center w-full">{{ props.title }}</span>
		</template>
		<template #end> </template>
	</Toolbar>
</template>
<script setup lang="ts">
import Toolbar from "primevue/toolbar";
import GoBack from "./GoBack.vue";

const props = defineProps<{
	title: string;
}>();

const emits = defineEmits<{
	goBack: [];
}>();
</script>
