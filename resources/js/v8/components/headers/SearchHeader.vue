<template>
	<UHeader :toggle="false" :ui="{ root: 'border-b-0', center: 'flex' }">
		<template #left>
			<GoBack @go-back="emits('goBack')" />
		</template>

		<div class="absolute top-0 py-3 left-1/2 -translate-x-1/2 h-14 flex items-center">
			<span class="sm:hidden font-bold">
				{{ $t("gallery.search.title") }}
			</span>
			<span class="hidden sm:block font-bold text-sm lg:text-base text-center pointer-events-none">{{ props.title }}</span>
		</div>
	</UHeader>
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
