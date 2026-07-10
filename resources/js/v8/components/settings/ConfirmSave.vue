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
			<UTooltip :text="$t('settings.all.back_to_settings')">
				<router-link
					:to="{ name: 'settings', params: { tab: '' } }"
					:class="{
						'flex items-center': true,
						hidden: !props.areAllSettingsEnabled,
					}"
				>
					<UButton :icon="isLTR() ? 'prime:angle-double-left' : 'prime:angle-double-right'" variant="ghost" color="primary" />
				</router-link>
			</UTooltip>
			<div
				:class="{
					'ltr:ml-6 rtl:mr-6 flex gap-2 items-center w-full': true,
					'opacity-50': !props.hasExperts,
					'opacity-100': props.hasExperts,
				}"
			>
				<USwitch v-model="is_expert_mode" :disabled="!props.hasExperts" :ui="{ label: 'text-muted flex items-center gap-2' }">
					<template #label>{{ $t("settings.all.expert_settings") }}<UIcon name="lucide:graduation-cap" size="1.25em" /></template>
				</USwitch>
			</div>
			<div class="flex items-center w-full justify-end">
				<UTooltip :text="$t('settings.all.old_setting_style')">
					<div
						:class="{
							'cursor-pointer px-4': true,
							'text-primary-400': is_old_style,
							'text-muted': !is_old_style,
						}"
					>
						<UIcon name="lucide:edit" @click="is_old_style = !is_old_style" />
					</div>
				</UTooltip>
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
	<div v-else class="sticky z-30 w-full top-0 flex bg-white dark:bg-neutral-800 h-auto lg:h-11">
		<UAlert color="warning" class="w-full ltr:rounded-r-none rtl:rounded-l-none" :description="$t('settings.all.change_detected')" />
		<UButton class="bg-error-800 text-white font-bold px-8 hover:bg-error-700 rtl:rounded-r-none ltr:rounded-l-none" @click="emits('save')">{{
			$t("settings.all.save")
		}}</UButton>
	</div>
</template>
<script setup lang="ts">
import SettingsService from "@/services/settings-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useLtRorRtL } from "@/utils/Helpers";
import { storeToRefs } from "pinia";
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
