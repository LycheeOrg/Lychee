<template>
	<MaintenanceRow v-if="data !== undefined && fixable">
		<template #title>{{ $t("maintenance.fix-tree.title") }}</template>
		<span v-if="!loading">{{ stats }}</span>
		<LycheeLoadingIcon fast v-if="loading" class="inline-block text-2xl" />
		<template #actions>
			<UButton variant="soft" v-if="fixable && !loading" :to="{ name: 'tree' }" color="primary">
				{{ $t("maintenance.fix-tree.button") }}
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
import { trans } from "laravel-vue-i18n";

const data = ref<App.Http.Resources.Diagnostics.TreeState | undefined>(undefined);
const loading = ref(false);
const toast = useAppToast();

const fixable = computed(() => {
	return data.value && (data.value.oddness > 0 || data.value.duplicates > 0 || data.value.wrong_parent > 0 || data.value.missing_parent > 0);
});
const stats = computed(() => {
	if (data.value === undefined) {
		return "";
	}
	return [
		`${trans("maintenance.fix-tree.Oddness")}: ${data.value.oddness}`,
		`${trans("maintenance.fix-tree.Duplicates")}: ${data.value.duplicates}`,
		`${trans("maintenance.fix-tree.Wrong parents")}: ${data.value.wrong_parent}`,
		`${trans("maintenance.fix-tree.Missing parents")}: ${data.value.missing_parent}`,
	].join(" · ");
});
function load() {
	loading.value = true;
	MaintenanceService.treeGet()
		.then((response) => {
			data.value = response.data;
			loading.value = false;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			loading.value = false;
		});
}

load();
</script>
