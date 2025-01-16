<template>
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<Button @click="togglableStore.toggleLeftMenu" icon="pi pi-bars" class="mr-2 border-none" severity="secondary" text />
		</template>

		<template #center>
			{{ $t("diagnostics.title") }}
		</template>

		<template #end>
			<Button :disabled="!canCopy" text aria-label="Copy" icon="pi pi-copy" v-tooltip="$t('diagnostics.copy_to_clipboard')" @click="copy" />
		</template>
	</Toolbar>
	<ErrorsDiagnotics @loaded="loadError" />
	<InfoDiagnostics v-if="user?.id" @loaded="loadInfo" />
	<SpaceDiagnostics v-if="user?.id" />
	<ConfigurationsDiagnostics v-if="user?.id" @loaded="loadConfig" />
</template>
<script setup lang="ts">
import { ref, computed } from "vue";
import Toolbar from "primevue/toolbar";
import Button from "primevue/button";
import ConfigurationsDiagnostics from "@/components/diagnostics/ConfigurationsDiagnostics.vue";
import InfoDiagnostics from "@/components/diagnostics/InfoDiagnostics.vue";
import ErrorsDiagnotics from "@/components/diagnostics/ErrorsDiagnostics.vue";
import SpaceDiagnostics from "@/components/diagnostics/SpaceDiagnostics.vue";
import { useAuthStore } from "@/stores/Auth";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
authStore.getUser();
const togglableStore = useTogglablesStateStore();

const toast = useToast();

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

function copy() {
	if (!canCopy) {
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
