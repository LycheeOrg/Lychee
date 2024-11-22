<template>
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<Button @click="togglableStore.toggleLeftMenu" icon="pi pi-bars" class="mr-2 border-none" severity="secondary" text />
		</template>

		<template #center>
			{{ $t("lychee.DIAGNOSTICS") }}
		</template>

		<template #end>
			<Button :disabled="!canCopy" text aria-label="Copy" icon="pi pi-copy" v-tooltip="'Copy diagnostics to clipboard'" @click="copy" />
		</template>
	</Toolbar>
	<ErrorsDiagnotics @loaded="errorLoaded = true" />
	<InfoDiagnostics v-if="user?.id" @loaded="infoLoaded = true" />
	<SpaceDiagnostics v-if="user?.id" />
	<ConfigurationsDiagnostics v-if="user?.id" @loaded="configurationLoaded = true" />
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

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
authStore.getUser();
const togglableStore = useTogglablesStateStore();

const toast = useToast();
const errorLoaded = ref<boolean>(false);
const infoLoaded = ref<boolean>(false);
const configurationLoaded = ref<boolean>(false);

const canCopy = computed(() => errorLoaded.value && infoLoaded.value && configurationLoaded.value);

function copy() {
	const errors = document.getElementById("ErrorsData");
	const info = document.getElementById("InfoData");
	const Configuration = document.getElementById("ConfigurationData");
	const errorLines = errors?.innerText.split("\n") ?? [];
	let errorText = "";
	for (let i = 0; i < errorLines.length; i+=2) {
		errorText += `${errorLines[i].padEnd(7)}: ${errorLines[i + 1]}\n`;
	}

	const toClipBoard = `Errors:\n${"-".repeat(20)}\n${errorText}\n\n\nInfo:\n${"-".repeat(20)}\n${info?.innerText}\n\n\nConfig:\n${"-".repeat(20)}\n${Configuration?.innerText}`;

	navigator.clipboard
		.writeText(toClipBoard)
		.then(() => toast.add({ severity: "info", summary: "Info", detail: "Diagnostic copied to clipboard", life: 3000 }));
}
</script>
