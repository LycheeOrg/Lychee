<template>
	<nav id="header-breadcrumb" class="hidden sm:flex flex-row-reverse justify-end max-w-[45vw] items-center overflow-hidden whitespace-nowrap h-12">
		<span class="text-base truncate max-w-32 shrink-0">{{ currentTitle }}</span>
		<template v-for="(item, index) in reversedItems" :key="item.id ?? index">
			<UIcon :name="isLTR() ? 'lucide:chevron-right' : 'lucide:chevron-left'" class="text-sm mx-1 text-muted shrink-0" />
			<RouterLink
				v-if="item.id !== null"
				:to="{ name: 'album', params: { albumId: item.id } }"
				class="text-base truncate max-w-32 text-muted hover:text-default transition"
			>
				{{ item.title }}
			</RouterLink>
			<span v-else class="text-base truncate max-w-32 text-muted">{{ item.title }}</span>
		</template>
		<!-- Leftmost item of the trail (the row is reversed). A plain "up one level" arrow is
		     redundant here since every ancestor above is already a link, so this anchors the trail
		     at the gallery root instead. -->
		<UButton
			as="router-link"
			:to="{ name: 'gallery' }"
			icon="lucide:home"
			:aria-label="$t('left-menu.back_to_gallery')"
			color="neutral"
			variant="ghost"
			:class="isLTR() ? 'mr-2' : 'ml-2'"
		/>
	</nav>
	<div class="flex sm:hidden">
		<GoBack @go-back="emits('goBack')" />
		<span>{{ currentTitle }}</span>
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { useLtRorRtL } from "@/utils/Helpers";
import GoBack from "./GoBack.vue";

const props = defineProps<{
	items: App.Http.Resources.Models.BreadcrumbItemResource[];
	currentTitle: string;
}>();

const { isLTR } = useLtRorRtL();

const emits = defineEmits<{
	goBack: [];
}>();

const reversedItems = computed(() => [...props.items].reverse());
</script>
