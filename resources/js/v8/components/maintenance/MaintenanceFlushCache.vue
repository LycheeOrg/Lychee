<template>
	<MaintenanceRow>
		<template #title>{{ $t("maintenance.flush-cache.title") }}</template>
		<span v-if="!loading">{{ $t("maintenance.flush-cache.description") }}</span>
		<LycheeLoadingIcon fast v-if="loading" class="inline-block text-2xl" />
		<template #actions>
			<UButton variant="soft" v-if="!loading" color="warning" @click="exec">
				{{ $t("maintenance.flush-cache.button") }}
			</UButton>
		</template>
	</MaintenanceRow>
</template>

<script setup lang="ts">
import { ref } from "vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import MaintenanceRow from "@/v8/components/maintenance/MaintenanceRow.vue";
import MaintenanceService from "@/services/maintenance-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";

const loading = ref(false);
const toast = useAppToast();

function exec() {
	loading.value = true;
	MaintenanceService.flushDo()
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
			loading.value = false;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			loading.value = false;
		});
}
</script>
