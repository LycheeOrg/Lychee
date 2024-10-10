<template>
	<Dialog v-model:visible="visible" modal pt:root:class="border-none" @hide="closeCallback">
		<template #container="{ closeCallback }">
			<div
				class="flex flex-col gap-4 bg-gradient-to-b from-bg-300 to-bg-400 relative rounded-md text-muted-color max-w-xl lg:max-w-3xl xl:max-w-7xl"
			>
				<h1 class="text-center text-xl font-bold w-full border-b border-b-black/20 p-3">Keyboard shortcuts</h1>
				<div class="flex flex-wrap gap-2 justify-center align-top max-h-[80vh] overflow-y-auto px-3">
					<DataTable v-for="list in shortcutsList" :value="list.shortcuts" :showGridlines="false" :size="'small'">
						<template #header>
							<span class="font-bold">{{ list.header }}</span>
						</template>
						<Column field="action" headerClass="hidden" class="text-sm"></Column>
						<Column field="key" headerClass="hidden" class="text-right text-sm">
							<template #body="slotProps">
								<kbd
									v-for="k in slotProps.data.key.split(' ')"
									class="py-0.5 px-2 ml-2 rounded border border-black/30 text-xs shadow-black/5 bg-bg-300 shadow-sm font-mono"
								>
									{{ k }}
								</kbd>
							</template>
						</Column>
					</DataTable>
					<div class="w-full flex justify-center my-4">
						<Checkbox v-model="doNotShowAgain" :binary="true" inputId="doNotShowAgain" />
						<label for="doNotShowAgain" class="ml-2 text-sm text-muted-color"> Don't show this again </label>
					</div>
				</div>
				<div class="flex justify-center">
					<Button @click="closeCallback" severity="secondary" class="w-full font-bold border-none rounded-none rounded-bl-xl rounded-br-xl">
						{{ $t("lychee.CLOSE") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import SettingsService from "@/services/settings-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import Button from "primevue/button";
import Column from "primevue/column";
import Checkbox from "primevue/checkbox";
import DataTable from "primevue/datatable";
import Dialog from "primevue/dialog";
import { ref } from "vue";
import { useToast } from "primevue/usetoast";
import { onKeyStroke } from "@vueuse/core";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";

const visible = defineModel("visible", { default: false });

const toast = useToast();
const doNotShowAgain = ref(false);
const LycheeState = useLycheeStateStore();
function closeCallback() {
	LycheeState.show_keybinding_help_popup = false;
	visible.value = false;

	if (doNotShowAgain.value) {
		SettingsService.setConfigs({
			configs: [
				{
					key: "show_keybinding_help_popup",
					value: "0",
				},
			],
		}).then(() => {
			toast.add({
				severity: "success",
				summary: "We will keep it hiden.",
				life: 2000,
			});
		});
	}
}
const shortcutsList = ref([
	{
		header: "Site-wide Shortcuts",
		shortcuts: [
			{ action: "Back/Cancel", key: "Esc" },
			{ action: "Confirm", key: "Enter" },
			{ action: "Login", key: "l" },
			{ action: "Toggle full screen", key: "f" },
			{ action: "Toggle Sensitive albums", key: "h" },
			// { action: "Login with U2F", key: "k" },
		],
	},
	{
		header: "Albums Shortcuts",
		shortcuts: [
			{ action: "New album", key: "n" },
			{ action: "Upload photos", key: "u" },
			{ action: "Search", key: "/" },
			{ action: "Show this modal", key: "?" },
			// { action: "Toggle Sensitive albums", key: "h" },
			{ action: "Select all", key: "ctrl/cmd a" },
			{ action: "Move selection", key: "m" },
			{ action: "Delete selection", key: "BckSpace" },
		],
	},
	{
		header: "Album Shortcuts",
		shortcuts: [
			{ action: "New album", key: "n" },
			{ action: "Upload photos", key: "u" },
			{ action: "Search", key: "/" },
			{ action: "Start/Stop slideshow", key: "Space" },
			{ action: "Select all", key: "ctrl/cmd a" },
			{ action: "Move selection", key: "m" },
			{ action: "Delete selection", key: "BckSpace" },
			{ action: "Toggle panel", key: "i" },
			// { action: "Select all albums or photos", key: "ctrl a" },
		],
	},
	{
		header: "Photo Shortcuts",
		shortcuts: [
			{ action: "Previous photo", key: "←" },
			{ action: "Next photo", key: "→" },
			// { action: 'Rate 1 star', key: "1" },
			// { action: 'Rate 2 star', key: "2" },
			// { action: 'Rate 3 star', key: "3" },
			// { action: 'Rate 4 star', key: "4" },
			// { action: 'Rate 5 star', key: "5" },
			// TODO: implement this.
			{ action: "Cycle overlay mode", key: "o" },
			{ action: "Start/Stop slideshow", key: "Space" },
			{ action: "Star photo", key: "s" },
			{ action: "Move photo", key: "m" },
			{ action: "Delete the photo", key: "BckSpace" },
			{ action: "Edit information", key: "e" },
			{ action: "Show information", key: "i" },
			// { action: "Rotate counter clock wise", key: "ctrl ←" },
			// { action: "Rotate clockwise", key: "ctrl →" },
		],
	},
]);

onKeyStroke("?", () => !shouldIgnoreKeystroke() && (visible.value = true));
</script>
