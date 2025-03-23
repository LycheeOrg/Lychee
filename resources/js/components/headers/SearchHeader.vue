<template>
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="emits('goBack')" />
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
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";

const props = defineProps<{
	title: string;
}>();

const emits = defineEmits<{
	goBack: [];
}>();
</script>
