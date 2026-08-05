<template>
	<MaintenanceRow v-if="data !== undefined && data !== 0">
		<template #title>{{ $t("maintenance.purge-orphan-face-embeddings.title") }}</template>
		<span v-if="!loading" v-html="description"></span>
		<LycheeLoadingIcon fast v-if="loading" class="inline-block text-2xl" />
		<template #actions>
			<UButton variant="soft" v-if="data !== 0 && !loading" color="warning" @click="exec">
				{{ $t("maintenance.purge-orphan-face-embeddings.action") }}
			</UButton>
		</template>
	</MaintenanceRow>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import MaintenanceRow from "@/v8/components/maintenance/MaintenanceRow.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import MaintenanceService from "@/services/maintenance-service";

const emit = defineEmits<{ purged: [] }>();

const data = ref<number | undefined>(undefined);
const loading = ref(false);
const toast = useAppToast();

const description = computed(() => {
	if (data.value === 0) {
		return "";
	}
	return sprintf(trans("maintenance.purge-orphan-face-embeddings.description"), data.value);
});

function load() {
	loading.value = true;
	MaintenanceService.purgeOrphanFaceEmbeddingsCheck()
		.then((response) => {
			data.value = response.data;
			loading.value = false;
		})
		.catch((e) => {
			if (e.response.status !== 501 && e.response.data.message !== "Feature 'v8' is disabled") {
				toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			}
			loading.value = false;
		});
}

function exec() {
	loading.value = true;
	MaintenanceService.purgeOrphanFaceEmbeddingsDo()
		.then(() => {
			toast.add({
				severity: "success",
				summary: trans("toasts.success"),
				detail: trans("maintenance.purge-orphan-face-embeddings.success"),
				life: 3000,
			});
			loading.value = false;
			emit("purged");
		})
		.catch((e) => {
			if (e.response.status !== 501 && e.response.data.message !== "Feature 'v8' is disabled") {
				toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			}
			loading.value = false;
		})
		.finally(load);
}

load();
</script>
