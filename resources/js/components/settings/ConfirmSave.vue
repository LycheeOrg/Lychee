<template>
	<div
		:class="{
			'flex w-full overflow-hidden transition-all duration-200 ease-out': true,
			'h-11': !isGeneral,
			'h-0': isGeneral,
		}"
		v-if="!props.isVisible"
	>
		<div
			:class="{
				'shrink-0 transition-all duration-200 ease-out': true,
				'w-3xs': !are_all_settings_enabled,
				'w-0': are_all_settings_enabled,
			}"
		></div>
		<div class="flex w-full gap-4 justify-between pl-6">
			<div class="flex gap-2 items-center w-full">
				<ToggleSwitch v-model="is_expert_mode" input-id="expertModeToggle"></ToggleSwitch>
				<label for="expertModeToggle" class="text-muted-color">{{ $t("Expert Mode") }}<i class="pi pi-graduation-cap ml-2"></i></label>
			</div>
			<div class="flex gap-2 items-center w-full">
				<ToggleSwitch v-model="is_old_style" input-id="oldStyleToggle"></ToggleSwitch>
				<label for="oldStyleToggle" class="text-muted-color"
					>{{ $t("settings.all.old_setting_style") }}<i class="pi pi-pen-to-square ml-2"></i
				></label>
			</div>
			<div class="flex gap-2 items-center w-full" :class="{ ' invisible': !is_expert_mode && !are_all_settings_enabled }">
				<ToggleSwitch v-model="are_all_settings_enabled" input-id="allSettingsToggle"></ToggleSwitch>
				<label for="allSettingsToggle" class="text-muted-color">{{ $t("settings.all.all_settings") }}<i class="pi pi-cog ml-2"></i></label>
			</div>
		</div>
	</div>
	<div v-else class="sticky z-30 w-full top-0 flex bg-white dark:bg-surface-800 h-11">
		<Message severity="warn" class="w-full">{{ $t("settings.all.change_detected") }}</Message>
		<Button @click="emits('save')" class="bg-danger-800 border-none text-white font-bold px-8 hover:bg-danger-700">{{
			$t("settings.all.save")
		}}</Button>
	</div>
</template>
<script setup lang="ts">
import SettingsService from "@/services/settings-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import Button from "primevue/button";
import Message from "primevue/message";
import ToggleSwitch from "primevue/toggleswitch";
import { onMounted } from "vue";

const props = defineProps<{
	isVisible: boolean;
	isGeneral: boolean;
}>();

const lycheeStore = useLycheeStateStore();
const { is_old_style, is_expert_mode, are_all_settings_enabled } = storeToRefs(lycheeStore);

const emits = defineEmits<{
	save: [];
	ready: [];
}>();

function load() {
	SettingsService.init().then((response) => {
		lycheeStore.is_old_style = response.data.default_old_settings;
		lycheeStore.is_expert_mode = response.data.default_expert_settings;
		lycheeStore.are_all_settings_enabled = response.data.default_all_settings;
		emits("ready");
	});
}

onMounted(() => {
	load();
});
</script>
