<template>
	<UCard v-if="data !== undefined && data > 0" class="min-h-40 relative" :ui="{ body: 'h-full flex flex-col justify-between gap-4' }">
		<template #header>
			<div class="text-center">
				{{ title }}
			</div>
		</template>
		<div class="w-full h-40 overflow-y-auto text-sm text-muted">
			<div class="w-full text-center" v-html="description"></div>
			<Spinner v-if="loading" class="w-full" />
		</div>
		<div class="flex gap-4 mt-1">
			<UButton v-if="data > 0 && !loading" color="primary" class="w-full justify-center" @click="exec">
				{{ $t("maintenance.gen-sizevariants.button") }}
			</UButton>
		</div>
	</UCard>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import Spinner from "@/v8/components/Spinner.vue";
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
