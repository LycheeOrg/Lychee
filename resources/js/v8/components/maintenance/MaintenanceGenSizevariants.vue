<template>
	<MaintenanceRow v-if="data !== undefined && data > 0">
		<template #title>{{ title }}</template>
		<span v-html="description"></span>
		<LycheeLoadingIcon fast v-if="loading" class="inline-block text-2xl" />
		<template #actions>
			<UButton variant="soft" v-if="data > 0 && !loading" color="primary" @click="exec">
				{{ $t("maintenance.gen-sizevariants.button") }}
			</UButton>
		</template>
	</MaintenanceRow>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import MaintenanceRow from "@/v8/components/maintenance/MaintenanceRow.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import MaintenanceService from "@/services/maintenance-service";
import { sprintf } from "sprintf-js";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	sv: App.Enum.SizeVariantType;
}>();

const data = ref<number | undefined>(undefined);
const loading = ref(false);
const toast = useAppToast();

const title = computed(() => {
	return sprintf(trans("maintenance.gen-sizevariants.title"), getName(props.sv));
});
const description = computed(() => {
	return sprintf(trans("maintenance.gen-sizevariants.description"), data.value, getName(props.sv));
});

function getName(sv: App.Enum.SizeVariantType): string {
	switch (sv) {
		case 8:
			return "placeholder";
		case 7:
			return "thumb";
		case 6:
			return "thumb2x";
		case 5:
			return "small";
		case 4:
			return "small2x";
		case 3:
			return "medium";
		case 2:
			return "medium2x";
		case 1:
			return "original";
		case 0:
			return "raw";
	}
}

function load() {
	MaintenanceService.genSizeVariantsCheck(props.sv).then((response) => {
		data.value = response.data;
		loading.value = false;
	});
}

function exec() {
	loading.value = true;
	MaintenanceService.genSizeVariantsDo(props.sv)
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
			load();
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			loading.value = false;
		});
}

load();
</script>
