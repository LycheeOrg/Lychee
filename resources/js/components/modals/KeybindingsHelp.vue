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
						<label for="doNotShowAgain" class="ml-2 text-sm text-muted-color">{{ $t("dialogs.keybindings.don_t_show_again") }}</label>
					</div>
				</div>
				<div class="flex justify-center">
					<Button @click="closeCallback" severity="secondary" class="w-full font-bold border-none rounded-none rounded-bl-xl rounded-br-xl">
						{{ $t("dialogs.button.close") }}
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
import { trans } from "laravel-vue-i18n";

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
				summary: trans("dialogs.keybindings.keep_hidden"),
				life: 2000,
			});
		});
	}
}
const shortcutsList = ref([
	{
		header: trans("dialogs.keybindings.side_wide"),
		shortcuts: [
			{ action: trans("back_cancel"), key: "Esc" },
			{ action: trans("confirm"), key: "Enter" },
			{ action: trans("login"), key: "l" },
			{ action: trans("toggle_full_screen"), key: "f" },
			{ action: trans("toggle_sensitive_albums"), key: "h" },
			// { action: "Login with U2F", key: "k" },
		],
	},
	{
		header: trans("dialogs.keybindings.albums"),
		shortcuts: [
			{ action: trans("dialogs.keybindings.new_album"), key: "n" },
			{ action: trans("dialogs.keybindings.upload_photos"), key: "u" },
			{ action: trans("dialogs.keybindings.search"), key: "/" },
			{ action: trans("dialogs.keybindings.show_this_modal"), key: "?" },
			{ action: trans("dialogs.keybindings.select_all"), key: "ctrl/cmd a" },
			{ action: trans("dialogs.keybindings.move_selection"), key: "m" },
			{ action: trans("dialogs.keybindings.delete_selection"), key: "BckSpace" },
		],
	},
	{
		header: trans("dialogs.keybindings.album"),
		shortcuts: [
			{ action: trans("dialogs.keybindings.new_album"), key: "n" },
			{ action: trans("dialogs.keybindings.upload_photos"), key: "u" },
			{ action: trans("dialogs.keybindings.search"), key: "/" },
			{ action: trans("dialogs.keybindings.slideshow"), key: "Space" },
			{ action: trans("dialogs.keybindings.select_all"), key: "ctrl/cmd a" },
			{ action: trans("dialogs.keybindings.move_selection"), key: "m" },
			{ action: trans("dialogs.keybindings.delete_selection"), key: "BckSpace" },
			{ action: trans("dialogs.keybindings.toggle"), key: "i" },
		],
	},
	{
		header: trans("dialogs.keybindings.photo"),
		shortcuts: [
			{ action: trans("dialogs.keybindings.previous"), key: "←" },
			{ action: trans("dialogs.keybindings.next"), key: "→" },
			// { action: 'Rate 1 star', key: "1" },
			// { action: 'Rate 2 star', key: "2" },
			// { action: 'Rate 3 star', key: "3" },
			// { action: 'Rate 4 star', key: "4" },
			// { action: 'Rate 5 star', key: "5" },
			// TODO: implement this.
			{ action: trans("dialogs.keybindings.cycle"), key: "o" },
			{ action: trans("dialogs.keybindings.slideshow"), key: "Space" },
			{ action: trans("dialogs.keybindings.star"), key: "s" },
			{ action: trans("dialogs.keybindings.move"), key: "m" },
			{ action: trans("dialogs.keybindings.delete"), key: "BckSpace" },
			{ action: trans("dialogs.keybindings.edit"), key: "e" },
			{ action: trans("dialogs.keybindings.show_hide_meta"), key: "i" },
			// { action: "Rotate counter clock wise", key: "ctrl ←" },
			// { action: "Rotate clockwise", key: "ctrl →" },
		],
	},
]);

onKeyStroke("?", () => !shouldIgnoreKeystroke() && (visible.value = true));
</script>
