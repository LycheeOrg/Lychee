<template>
	<!--
		Rendered right-to-left (flex-row-reverse) over a reversed item list so that, when the
		chain overflows max-w-[45vw], the container's overflow-hidden clips the oldest/least
		relevant ancestors first and currentTitle - anchored at the start of the reversed flex
		flow - always stays visible. The double reversal keeps the visual reading order intact.
	-->
	<nav
		id="header-breadcrumb"
		class="hidden @min-[28rem]:flex flex-row-reverse justify-end max-w-[45vw] items-center overflow-hidden whitespace-nowrap h-12"
	>
		<span class="text-base truncate max-w-32 shrink-0">{{ currentTitle }}</span>
		<template v-for="(item, index) in reversedItems" :key="item.id ?? index">
			<i :class="isLTR() ? 'pi-angle-right' : 'pi-angle-left'" class="pi text-sm mx-1 text-muted-color shrink-0" />
			<RouterLink
				v-if="item.id !== null"
				:to="{ name: 'album', params: { albumId: item.id } }"
				class="text-base truncate max-w-32 text-muted-color hover:text-color transition"
			>
				{{ item.title }}
			</RouterLink>
			<span v-else class="text-base truncate max-w-32 text-muted-color">{{ item.title }}</span>
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
