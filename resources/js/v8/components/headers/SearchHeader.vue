<template>
	<UHeader :toggle="false">
		<template #title>
			<LycheeBreadcrumb
				v-if="scopedAlbumTitle && showBreadcrumb"
				:items="breadcrumbItems ?? []"
				:current-title="scopedAlbumTitle"
				@go-back="emits('goBack')"
			/>
			<GoBack v-else @go-back="emits('goBack')" />
		</template>

		<div class="flex flex-col items-center justify-center gap-1 max-w-[45vw]">
			<span class="font-bold text-sm lg:text-base text-center pointer-events-none">{{ props.title }}</span>
			<span
				v-if="scopedAlbumTitle && !showBreadcrumb"
				class="flex items-center gap-1 max-w-full rounded-full border border-default bg-elevated/50 px-2.5 py-0.5 text-xs text-muted"
			>
				<UIcon name="lucide:folder" class="shrink-0 size-3" />
				<span class="truncate">{{ scopedAlbumTitle }}</span>
			</span>
		</div>

		<template #right>
			<UButton
				v-if="scopedAlbumTitle"
				icon="lucide:x"
				size="sm"
				color="neutral"
				variant="ghost"
				:aria-label="$t('gallery.search.clear_scope')"
				@click="emits('clearScope')"
			/>
		</template>
	</UHeader>
</template>
<script setup lang="ts">
import GoBack from "./GoBack.vue";
import LycheeBreadcrumb from "./LycheeBreadcrumb.vue";

const props = defineProps<{
	title: string;
	scopedAlbumTitle?: string;
	showBreadcrumb?: boolean;
	breadcrumbItems?: App.Http.Resources.Models.BreadcrumbItemResource[];
}>();

const emits = defineEmits<{
	goBack: [];
	clearScope: [];
}>();
</script>
