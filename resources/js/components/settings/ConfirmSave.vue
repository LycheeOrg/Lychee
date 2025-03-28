<template>
	<div
		:class="{
			'flex overflow-hidden transition-all duration-200 ease-out': true,
			'h-auto lg:h-11': !isCollapsed,
			'h-0': isCollapsed,
		}"
		v-if="!props.isSaveVisible"
	>
		<div
			:class="{
				'shrink-0 transition-all duration-200 ease-out': true,
				'w-0 lg:w-3xs': !props.areAllSettingsEnabled,
				'w-0': props.areAllSettingsEnabled,
			}"
		></div>
		<div class="flex flex-wrap lg:flex-nowrap w-full gap-4 justify-start">
			<router-link
				:to="{ name: 'settings', params: { tab: '' } }"
				:class="{
					'flex items-center': true,
					hidden: !props.areAllSettingsEnabled,
				}"
				v-tooltip.bottom="{ value: $t('settings.all.back_to_settings'), pt: pt }"
			>
				<Button icon="pi pi-angle-double-left" class="border-none py-2" severity="primary" text />
			</router-link>
			<div
				:class="{
					'ml-6 flex gap-2 items-center w-full': true,
					'opacity-50': !props.hasExperts,
					'opacity-100': props.hasExperts,
				}"
			>
				<ToggleSwitch v-model="is_expert_mode" input-id="expertModeToggle" :disabled="!props.hasExperts"></ToggleSwitch>
				<label for="expertModeToggle" class="text-muted-color">{{ $t("Expert Mode") }}<i class="pi pi-graduation-cap ml-2"></i></label>
			</div>
			<div class="flex items-center w-full justify-end">
				<i
					:class="{
						'pi pi-pen-to-square cursor-pointer px-4': true,
						'text-primary-400': is_old_style,
						'text-muted-color': !is_old_style,
					}"
					@click="is_old_style = !is_old_style"
					v-tooltip.left="$t('settings.all.old_setting_style')"
				>
				</i>
			</div>
		</div>
	</div>
	<div v-else class="sticky z-30 w-full top-0 flex bg-white dark:bg-surface-800 h-auto lg:h-11">
		<Message severity="warn" class="w-full">{{ $t("settings.all.change_detected") }}</Message>
		<Button @click="emits('save')" class="bg-danger-800 border-none text-white font-bold px-8 hover:bg-danger-700">{{
			$t("settings.all.save")
		}}</Button>
	</div>
</template>
<script setup lang="ts">
import SettingsService from "@/services/settings-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { trans } from "laravel-vue-i18n";
import { storeToRefs } from "pinia";
import Button from "primevue/button";
import Message from "primevue/message";
import ToggleSwitch from "primevue/toggleswitch";
import { onMounted } from "vue";

const props = defineProps<{
	isSaveVisible: boolean;
	hasExperts: boolean;
	isCollapsed: boolean;
	areAllSettingsEnabled: boolean;
}>();

const lycheeStore = useLycheeStateStore();
const { is_old_style, is_expert_mode } = storeToRefs(lycheeStore);

const emits = defineEmits<{
	save: [];
	ready: [];
}>();

const pt = {
	root: {
		style: {
			transform: "translateX(40%)",
		},
	},
};

function load() {
	SettingsService.init().then((response) => {
		lycheeStore.is_old_style = response.data.default_old_settings;
		lycheeStore.is_expert_mode = response.data.default_expert_settings;
		emits("ready");
	});
}

onMounted(() => {
	load();
});
</script>
