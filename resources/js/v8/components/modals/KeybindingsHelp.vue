<template>
	<UModal v-model:open="open" :dismissible="true" :ui="{ content: 'max-w-xl lg:max-w-3xl xl:max-w-7xl' }">
		<template #header>
			<h1 class="text-center text-xl font-bold w-full">{{ $t("dialogs.keybindings.header") }}</h1>
		</template>
		<template #body>
			<div class="flex flex-wrap gap-4 justify-center align-top">
				<UTable
					v-for="(list, idx) in shortcutsList"
					:key="`list-${idx}`"
					:data="list.shortcuts"
					:columns="columns"
					class="max-w-2xs w-full"
					:ui="{ thead: 'hidden', td: 'px-2 py-1' }"
				>
					<template #key-cell="{ row }">
						<div class="ltr:text-right rtl:text-left">
							<UKbd
								v-for="k in (row.original.key as string).split(' ')"
								:key="`key-${idx}-${k}`"
								variant="subtle"
								class="ml-2"
								:value="k"
							/>
						</div>
					</template>
				</UTable>
				<div class="w-full flex justify-center mt-4 items-center gap-2">
					<UCheckbox v-model="doNotShowAgain" :ui="{ label: 'text-muted' }" :label="trans('dialogs.keybindings.don_t_show_again')" />
				</div>
				<div class="w-full flex justify-center mb-4 items-center gap-2">
					<UCheckbox v-model="hideHeaderButton" :ui="{ label: 'text-muted' }" :label="trans('dialogs.keybindings.hide_header_button')" />
				</div>
			</div>
		</template>
		<template #footer>
			<UButton color="neutral" variant="soft" class="w-full justify-center font-bold" @click="closeCallback">
				{{ $t("dialogs.button.close") }}
			</UButton>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import SettingsService from "@/services/settings-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { ref } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { onKeyStroke } from "@vueuse/core";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { trans } from "laravel-vue-i18n";
import type { TableColumn } from "@nuxt/ui";

const open = defineModel<boolean>("open", { default: false });

const toast = useAppToast();
const doNotShowAgain = ref(false);
const hideHeaderButton = ref(false);
const LycheeState = useLycheeStateStore();

const columns: TableColumn<{ action: string; key: string }>[] = [{ accessorKey: "action" }, { accessorKey: "key" }];

function closeCallback() {
	LycheeState.show_keybinding_help_popup = false;
	open.value = false;

	if (hideHeaderButton.value) {
		SettingsService.setConfigs({
			configs: [
				{
					key: "show_keybinding_help_button",
					value: "0",
				},
			],
		}).then(() => {
			toast.add({
				severity: "success",
				summary: trans("dialogs.keybindings.button_hidden"),
				life: 2000,
			});
		});
	}

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
			{ action: trans("dialogs.keybindings.back_cancel"), key: "Esc" },
			{ action: trans("dialogs.keybindings.confirm"), key: "Enter" },
			{ action: trans("dialogs.keybindings.login"), key: "l" },
			{ action: trans("dialogs.keybindings.toggle_full_screen"), key: "f" },
			{ action: trans("dialogs.keybindings.toggle_sensitive_albums"), key: "h" },
		],
	},
	{
		header: trans("dialogs.keybindings.albums"),
		shortcuts: [
			{ action: trans("dialogs.keybindings.new_album"), key: "n" },
			{ action: trans("dialogs.keybindings.upload_photos"), key: "u" },
			{ action: trans("dialogs.keybindings.search"), key: "/" },
			{ action: trans("dialogs.keybindings.show_this_modal"), key: "?" },
			{ action: trans("dialogs.keybindings.select_all"), key: "meta a" },
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
			{ action: trans("dialogs.keybindings.select_all"), key: "meta a" },
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
			{ action: trans("dialogs.keybindings.cycle"), key: "o" },
			{ action: trans("dialogs.keybindings.slideshow"), key: "Space" },
			{ action: trans("dialogs.keybindings.star"), key: "s" },
			{ action: trans("dialogs.keybindings.move"), key: "m" },
			{ action: trans("dialogs.keybindings.delete"), key: "BckSpace" },
			{ action: trans("dialogs.keybindings.edit"), key: "e" },
			{ action: trans("dialogs.keybindings.show_hide_meta"), key: "i" },
			{ action: trans("dialogs.keybindings.toggle_face_overlay"), key: "p" },
		],
	},
]);

onKeyStroke("?", () => !shouldIgnoreKeystroke() && (open.value = true));
</script>
