<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("diagnostics.title") }}
		<template #right>
			<UTooltip v-if="isSecureContext" :text="$t('diagnostics.copy_to_clipboard')">
				<UButton :disabled="!canCopy" variant="ghost" color="neutral" aria-label="Copy" icon="lucide:copy" @click="copy" />
			</UTooltip>
			<UTooltip v-else :text="$t('diagnostics.copy_on_secure_context')">
				<UButton :disabled="true" variant="ghost" color="neutral" aria-label="Copy" icon="lucide:copy" />
			</UTooltip>
		</template>
	</UHeader>
	<ErrorsDiagnotics @loaded="loadError" />
	<InfoDiagnostics v-if="user?.id" @loaded="loadInfo" />
	<SpaceDiagnostics v-if="user?.id" />
	<ConfigurationsDiagnostics v-if="user?.id" @loaded="loadConfig" />
</template>
<script setup lang="ts">
import { ref, computed } from "vue";
import ConfigurationsDiagnostics from "@/v8/components/diagnostics/ConfigurationsDiagnostics.vue";
import InfoDiagnostics from "@/v8/components/diagnostics/InfoDiagnostics.vue";
import ErrorsDiagnotics from "@/v8/components/diagnostics/ErrorsDiagnostics.vue";
import SpaceDiagnostics from "@/v8/components/diagnostics/SpaceDiagnostics.vue";
import { useUserStore } from "@/stores/UserState";
import { storeToRefs } from "pinia";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";

const userStore = useUserStore();
const { user } = storeToRefs(userStore);
userStore.load();

const toast = useAppToast();

const errorLoaded = ref<string[] | undefined>(undefined);
const infoLoaded = ref<string[] | undefined>(undefined);
const configurationLoaded = ref<string[] | undefined>(undefined);

const canCopy = computed(() => errorLoaded.value !== undefined && infoLoaded.value !== undefined && configurationLoaded.value !== undefined);

function loadError(val: string[]) {
	errorLoaded.value = val;
}

function loadInfo(val: string[]) {
	infoLoaded.value = val;
}

function loadConfig(val: string[]) {
	configurationLoaded.value = val;
}

const isSecureContext = computed(() => window.isSecureContext);

function copy() {
	if (!canCopy.value) {
		return;
	}

	const errorText: string = errorLoaded.value?.join("\n") ?? "";
	const infoText: string = infoLoaded.value?.join("\n") ?? "";
	const configurationText: string = configurationLoaded.value?.join("\n") ?? "";

	const toClipBoard = `Self-diagnosis:\n${"-".repeat(20)}\n${errorText}\n\n\nInfo:\n${"-".repeat(20)}\n${infoText}\n\n\nConfig:\n${"-".repeat(20)}\n${configurationText}`;

	navigator.clipboard
		.writeText(toClipBoard)
		.then(() => toast.add({ severity: "info", summary: trans("diagnostics.toast.info"), detail: trans("diagnostics.toast.copy"), life: 3000 }));
}
</script>
