<template>
	<nav
		id="header-breadcrumb"
		class="hidden @min-[28rem]:flex flex-row-reverse justify-end max-w-[45vw] items-center overflow-hidden whitespace-nowrap h-12"
	>
		<span class="text-base truncate max-w-32 shrink-0">{{ currentTitle }}</span>
		<template v-for="(item, index) in reversedItems" :key="item.id ?? index">
			<UIcon :name="isLTR() ? 'prime:angle-right' : 'prime:angle-left'" class="text-sm mx-1 text-muted shrink-0" />
			<RouterLink
				v-if="item.id !== null"
				:to="{ name: 'album', params: { albumId: item.id } }"
				class="text-base truncate max-w-32 text-muted hover:text-default transition"
			>
				{{ item.title }}
			</RouterLink>
			<span v-else class="text-base truncate max-w-32 text-muted">{{ item.title }}</span>
		</template>
	</nav>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { useLtRorRtL } from "@/utils/Helpers";

const props = defineProps<{
	items: App.Http.Resources.Models.BreadcrumbItemResource[];
	currentTitle: string;
}>();

const { isLTR } = useLtRorRtL();

const reversedItems = computed(() => [...props.items].reverse());
</script>
