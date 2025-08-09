<template>
	<template v-if="is_debug_enabled">
		<template v-if="lycheeError !== null">
			<div v-if="lycheeError.exception" class="w-full h-full fixed top-0 left-0 bg-panel z-50 flex flex-col">
				<Message severity="error" @click="closeError">
					<span v-if="lycheeError.exception" class="font-bold text-xl w-full"
						>{{ lycheeError.exception }} in {{ lycheeError.file }}:{{ lycheeError.line }}</span
					>
					<span v-else class="font-bold text-xl w-full">{{ lycheeError.message }}</span>
				</Message>
				<Panel class="h-full overflow-y-scroll">
					<template #header>
						<span class="font-bold text-xl">{{ lycheeError.message }}</span>
					</template>
					<template #icons>
						<Button icon="pi pi-times" severity="secondary" class="text-muted-color" rounded text @click="closeError" />
					</template>
					<Divider />
					<p v-for="(trace, idx) in lycheeError.trace" :key="'trace' + idx">
						{{ trace.file + ":" + trace.line }} &mdash; {{ trace.function }}
					</p>
				</Panel>
			</div>
			<div v-else-if="lycheeError.message">
				<Message severity="error" @click="closeError">
					<span class="font-bold text-xl w-full">{{ lycheeError.message }}</span>
				</Message>
			</div>
			<div v-else v-html="lycheeError"></div>
		</template>
		<div v-if="jsError !== null" class="w-full h-full absolute top-0 left-0 bg-panel z-50">
			<Message severity="error z-50" @click="closeError">
				<span class="font-bold text-xl">{{ jsError.message }} in {{ jsError.filename }}:{{ jsError.lineno }}</span>
			</Message>
		</div>
	</template>
	<SessionExpiredReload v-model:visible="sessionExpired" />
</template>
<script setup lang="ts">
import { ref } from "vue";
import Divider from "primevue/divider";
import Message from "primevue/message";
import Button from "primevue/button";
import Panel from "primevue/panel";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import SessionExpiredReload from "@/components/modals/SessionExpiredReload.vue";

type Trace = {
	class: string;
	file: string;
	line: number;
	function: string;
};

type ErrorEvent = {
	message: string;
	filename: string;
	lineno: number;
	colno: number;
	error: Error;
};

type LycheeException = {
	message: string;
	exception: string;
	file?: string;
	line?: number;
	trace?: Trace[];
	previous_exception?: LycheeException | null;
	details?: string;
};

const lycheeStore = useLycheeStateStore();
const { is_debug_enabled } = storeToRefs(lycheeStore);

const lycheeError = ref<LycheeException | null>(null);
const jsError = ref<ErrorEvent | null>(null);

const sessionExpired = ref(false);

window.addEventListener("error", function (e: ErrorEvent & { detail?: LycheeException; details?: LycheeException }) {
	console.log("error", e);
	if (e.details !== undefined) {
		lycheeError.value = e.details;
	} else if (e.detail !== undefined) {
		lycheeError.value = e.detail;
	} else {
		jsError.value = e;
	}
});

window.addEventListener("session_expired", function (_e: Event) {
	sessionExpired.value = true;
});

function closeError() {
	lycheeError.value = null;
	jsError.value = null;
}
</script>

<style lang="css">
.bg-panel {
	background-color: var(--p-surface-0);
}

.lychee-dark .bg-panel {
	background-color: var(--p-surface-900);
}
</style>
