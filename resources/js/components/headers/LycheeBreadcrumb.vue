<template>
	<nav id="header-breadcrumb" class="hidden @min-[28rem]:flex max-w-[45vw] items-center overflow-hidden whitespace-nowrap h-12">
		<template v-for="(item, index) in items" :key="item.id ?? index">
			<RouterLink
				v-if="item.id !== null"
				:to="{ name: 'album', params: { albumId: item.id } }"
				class="text-base truncate max-w-32 text-muted-color hover:text-color transition"
			>
				{{ item.title }}
			</RouterLink>
			<span v-else class="text-base truncate max-w-32 text-muted-color">{{ item.title }}</span>
			<i :class="isLTR() ? 'pi-angle-right' : 'pi-angle-left'" class="pi text-sm mx-1 text-muted-color shrink-0" />
		</template>
		<span class="text-base truncate max-w-32">{{ currentTitle }}</span>
	</nav>
</template>
<script setup lang="ts">
import { useLtRorRtL } from "@/utils/Helpers";

defineProps<{
	items: App.Http.Resources.Models.BreadcrumbItemResource[];
	currentTitle: string;
}>();

const { isLTR } = useLtRorRtL();
</script>
