<template>
	<div class="renamer-rule-line flex items-center justify-between p-3 border-b border-surface-200 hover:bg-surface-50 transition-colors">
		<div class="flex items-center space-x-3 flex-grow">
			<!-- Order and enabled status -->
			<div class="flex items-center space-x-2">
				<span class="text-sm font-semibold text-muted-color min-w-[2rem]">{{ rule.order }}</span>
				<div class="flex items-center">
					<div
						:class="{
							'w-2 h-2 rounded-full': true,
							'bg-green-500': rule.is_enabled,
							'bg-red-500': !rule.is_enabled,
						}"
					></div>
				</div>
			</div>

			<!-- Rule info -->
			<div class="flex-grow">
				<div class="flex items-center space-x-3">
					<h4 class="text-sm font-medium text-surface-900">{{ rule.rule }}</h4>
					<Tag :value="rule.mode" severity="secondary" size="small" />
				</div>
				<p v-if="rule.description" class="text-xs text-muted-color mt-1">{{ rule.description }}</p>
				<div class="text-xs text-muted-color mt-1">
					<span class="font-medium">Pattern:</span> {{ rule.needle }} →
					<span class="font-medium">Replace:</span> {{ rule.replacement }}
				</div>
			</div>
		</div>

		<!-- Actions -->
		<div class="flex items-center space-x-2">
			<Button
				v-if="canEdit"
				text
				severity="secondary"
				size="small"
				icon="pi pi-pencil"
				class="p-1"
				@click="$emit('edit')"
			/>
			<Button
				v-if="canDelete"
				text
				severity="danger"
				size="small"
				icon="pi pi-trash"
				class="p-1"
				@click="$emit('delete')"
			/>
		</div>
	</div>
</template>

<script setup lang="ts">
import Button from "primevue/button";
import Tag from "primevue/tag";

defineProps<{
	rule: App.Http.Resources.Models.RenamerRuleResource;
	canEdit: boolean;
	canDelete: boolean;
}>();

defineEmits<{
	edit: [];
	delete: [];
}>();
</script>
