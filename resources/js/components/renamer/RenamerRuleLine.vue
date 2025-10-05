<template>
	<div class="flex items-center justify-between p-3 hover:bg-surface-50 dark:hover:bg-surface-950 transition-colors">
		<div class="flex items-center gap-8 flex-grow">
			<!-- Order and enabled status -->
			<div class="flex items-center gap-4 justify-end">
				<span class="text-sm font-semibold text-muted-color min-w-[2rem] ltr:text-right rtl:text-left">{{ rule.order }}</span>
				<div class="flex items-center">
					<div :class="{ 'w-2 h-2 rounded-full': true, 'bg-green-500': rule.is_enabled, 'bg-red-500': !rule.is_enabled }"></div>
				</div>
				<div class="flex flex-col gap-2">
					<Tag
						:value="$t('renamer.photo')"
						v-if="rule.is_photo_rule"
						severity="success"
						rounded
						class="px-2 py-0.25 text-2xs ltr:mr-2 rtl:ml-2"
					/>
					<Tag :value="$t('renamer.album')" v-if="rule.is_album_rule" severity="warn" rounded class="px-2 py-0.25 text-2xs" />
				</div>
			</div>

			<!-- Rule info -->
			<div class="flex-grow flex-col justify-start">
				<div class="flex items-center gap-4">
					<h4 class="text-sm font-bold">{{ rule.rule }}</h4>
					<Tag :value="rule.mode" severity="primary" rounded class="px-4 py-0.5" />
				</div>
				<p v-if="rule.description" class="text-xs text-muted-color mt-1">{{ rule.description }}</p>
			</div>
			<!-- This part might change later with more complex rules... -->
			<div class="text-xs text-muted-color mt-1 text-center flex-grow" v-show="hasNeddle">
				<span class="font-medium mx-2">{{ $t("renamer.pattern_label") }}:</span>
				<pre class="inline font-mono before:content-['`'] after:content-['`']">{{ rule.needle }}</pre>
				<span class="mx-2 rtl:hidden">&xrarr;</span><span class="mx-2 ltr:hidden">&xlarr;</span
				><span class="font-medium mx-2">{{ $t("renamer.replace_with_label") }}:</span>
				<pre class="inline font-mono before:content-['`'] after:content-['`']">{{ rule.replacement }}</pre>
			</div>
		</div>

		<!-- Actions -->
		<div class="flex items-center space-x-2">
			<Button v-if="canEdit" severity="contrast" size="small" icon="pi pi-pencil" class="p-1 border-none" @click="$emit('edit')" />
			<Button v-if="canDelete" severity="danger" size="small" icon="pi pi-trash" class="p-1 border-none" @click="$emit('delete')" />
		</div>
	</div>
</template>

<script setup lang="ts">
import Button from "primevue/button";
import Tag from "primevue/tag";
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
