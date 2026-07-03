<template>
	<UModal v-model:open="state.open" :title="state.options.title" @update:open="onUpdateOpen">
		<template #body>
			<p>{{ state.options.message }}</p>
		</template>
		<template #footer>
			<UButton
				:label="state.options.rejectLabel ?? 'Cancel'"
				color="neutral"
				variant="outline"
				@click="settleConfirmDialog(false)"
			/>
			<UButton :label="state.options.acceptLabel ?? 'Confirm'" :color="acceptColor" @click="settleConfirmDialog(true)" />
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { confirmDialogState as state, settleConfirmDialog } from "@/v8/composables/useConfirmDialog";

const acceptColor = computed(() => {
	switch (state.options.severity) {
		case "danger":
			return "error";
		case "warning":
			return "warning";
		default:
			return "primary";
	}
});

function onUpdateOpen(open: boolean): void {
	if (!open) {
		settleConfirmDialog(false);
	}
}
</script>
