<template>
	<div class="flex items-center justify-between p-3 hover:bg-elevated/50 transition-colors">
		<div class="flex items-center gap-8 grow">
			<!-- Order and enabled status -->
			<div class="flex items-center gap-4 justify-end">
				<span class="text-sm font-semibold text-muted min-w-8 ltr:text-right rtl:text-left">{{ rule.order }}</span>
				<div class="flex items-center">
					<div :class="{ 'w-2 h-2 rounded-full': true, 'bg-green-500': rule.is_enabled, 'bg-red-500': !rule.is_enabled }"></div>
				</div>
				<div class="flex flex-col gap-2">
					<UBadge v-if="rule.is_photo_rule" color="success" class="px-2 py-px text-2xs ltr:mr-2 rtl:ml-2">{{ $t("renamer.photo") }}</UBadge>
					<UBadge v-if="rule.is_album_rule" color="warning" class="px-2 py-px text-2xs">{{ $t("renamer.album") }}</UBadge>
				</div>
			</div>

			<!-- Rule info -->
			<div class="grow flex-col justify-start">
				<div class="flex items-center gap-4">
					<h4 class="text-sm font-bold">{{ rule.rule }}</h4>
					<UBadge color="primary" class="px-4 py-0.5">{{ rule.mode }}</UBadge>
				</div>
				<p v-if="rule.description" class="text-xs text-muted mt-1">{{ rule.description }}</p>
			</div>
			<!-- This part might change later with more complex rules... -->
			<div class="text-xs text-muted mt-1 text-center grow" v-show="hasNeddle">
				<span class="font-medium mx-2">{{ $t("renamer.pattern_label") }}:</span>
				<pre class="inline font-mono before:content-['`'] after:content-['`']">{{ rule.needle }}</pre>
				<span class="mx-2 rtl:hidden">&xrarr;</span><span class="mx-2 ltr:hidden">&xlarr;</span
				><span class="font-medium mx-2">{{ $t("renamer.replace_with_label") }}:</span>
				<pre class="inline font-mono before:content-['`'] after:content-['`']">{{ rule.replacement }}</pre>
			</div>
		</div>

		<!-- Actions -->
		<div class="flex items-center gap-2">
			<UButton v-if="canEdit" color="neutral" size="sm" icon="prime:pencil" @click="$emit('edit')" />
			<UButton v-if="canDelete" color="error" size="sm" icon="prime:trash" @click="$emit('delete')" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
	rule: App.Http.Resources.Models.RenamerRuleResource;
	canEdit: boolean;
	canDelete: boolean;
}>();

defineEmits<{
	edit: [];
	delete: [];
}>();

const hasNeddle = computed(() => {
	return ["first", "all", "regex"].includes(props.rule.mode);
});
</script>
