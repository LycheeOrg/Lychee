<template>
	<div
		v-if="!props.isSaveVisible"
		:class="{
			'flex overflow-hidden transition-all duration-200 ease-out': true,
			'h-auto lg:h-11': !isCollapsed,
			'h-0': isCollapsed,
		}"
	>
		<div
			:class="{
				'shrink-0 transition-all duration-200 ease-out': true,
				'w-0 ltr:lg:w-3xs rtl:lg:w-3xs': !props.areAllSettingsEnabled,
				'w-0': props.areAllSettingsEnabled,
			}"
		></div>
		<div class="flex flex-wrap lg:flex-nowrap w-full gap-4 justify-start">
			<router-link
				v-tooltip.bottom="{ value: $t('settings.all.back_to_settings'), pt: pt }"
				:to="{ name: 'settings', params: { tab: '' } }"
				:class="{
					'flex items-center': true,
					hidden: !props.areAllSettingsEnabled,
				}"
			>
				<Button v-if="isLTR()" icon="pi pi-angle-double-left" class="border-none py-2" severity="primary" text />
				<Button v-else icon="pi pi-angle-double-right" class="border-none py-2" severity="primary" text />
			</router-link>
			<div
				:class="{
					'ltr:ml-6 rtl:mr-6 flex gap-2 items-center w-full': true,
					'opacity-50': !props.hasExperts,
					'opacity-100': props.hasExperts,
				}"
			>
				<ToggleSwitch v-model="is_expert_mode" input-id="expertModeToggle" :disabled="!props.hasExperts"></ToggleSwitch>
				<label for="expertModeToggle" class="text-muted-color"
					>{{ $t("settings.all.expert_settings") }}<i class="pi pi-graduation-cap ltr:ml-2 rtl:mr-2"></i
				></label>
			</div>
			<div class="flex items-center w-full justify-end">
				<i
					v-tooltip.left="$t('settings.all.old_setting_style')"
					:class="{
						'pi pi-pen-to-square cursor-pointer px-4': true,
						'text-primary-400': is_old_style,
						'text-muted-color': !is_old_style,
					}"
					@click="is_old_style = !is_old_style"
				>
				</i>
			</div>
			<div
				:class="{
					'shrink-0 transition-all duration-200 ease-out': true,
					'w-0': !props.areAllSettingsEnabled,
					'w-0 lg:w-3xs': props.areAllSettingsEnabled,
				}"
			></div>
		</div>
	</div>
	<div v-else class="sticky z-30 w-full top-0 flex bg-white dark:bg-surface-800 h-auto lg:h-11">
		<Message severity="warn" class="w-full">{{ $t("settings.all.change_detected") }}</Message>
		<Button class="bg-danger-800 border-none text-white font-bold px-8 hover:bg-danger-700" @click="emits('save')">{{
			$t("settings.all.save")
		}}</Button>
	</div>
</template>
<script setup lang="ts">
import SettingsService from "@/services/settings-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useLtRorRtL } from "@/utils/Helpers";
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


const { isLTR } = useLtRorRtL();

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
