<template>
	<UCard v-if="data !== undefined && fixable" class="min-h-40 relative bg-muted/50">
		<template #header>
			<div class="text-center font-bold">
				{{ $t("maintenance.fix-tree.title") }}
			</div>
		</template>
		<div class="w-full h-40 overflow-y-auto text-sm text-muted">
			<div v-if="!loading" class="w-full ltr:text-left rtl:text-right">
				{{ $t("maintenance.fix-tree.Oddness") }}: {{ data.oddness }}<br />
				{{ $t("maintenance.fix-tree.Duplicates") }}: {{ data.duplicates }}<br />
				{{ $t("maintenance.fix-tree.Wrong parents") }}: {{ data.wrong_parent }}<br />
				{{ $t("maintenance.fix-tree.Missing parents") }}: {{ data.missing_parent }}<br />
			</div>
			<Spinner v-if="loading" class="w-full" />
		</div>
		<template #footer>
			<UButton v-if="fixable && !loading" :to="{ name: 'tree' }" color="primary" class="w-full justify-center">
				{{ $t("maintenance.fix-tree.button") }}
			</UButton>
		</template>
	</UCard>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import Spinner from "@/v8/components/Spinner.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import MaintenanceService from "@/services/maintenance-service";
import { trans } from "laravel-vue-i18n";

const data = ref<App.Http.Resources.Diagnostics.TreeState | undefined>(undefined);
const loading = ref(false);
const toast = useAppToast();

const fixable = computed(() => {
	return data.value && (data.value.oddness > 0 || data.value.duplicates > 0 || data.value.wrong_parent > 0 || data.value.missing_parent > 0);
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
