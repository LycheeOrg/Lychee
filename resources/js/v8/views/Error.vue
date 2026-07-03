<template>
	<template v-if="is_debug_enabled">
		<template v-if="lycheeError !== null">
			<div v-if="lycheeError.exception" class="w-full h-full fixed top-0 left-0 bg-default z-50 flex flex-col">
				<UAlert color="error" :title="lycheeError.exception ? `${lycheeError.exception} in ${lycheeError.file}:${lycheeError.line}` : lycheeError.message" @click="closeError" />
				<UCard class="h-full overflow-y-scroll">
					<template #header>
						<div class="flex items-center justify-between">
							<span class="font-bold text-xl">{{ lycheeError.message }}</span>
							<UButton icon="prime:times" color="neutral" variant="ghost" @click="closeError" />
						</div>
					</template>
					<USeparator />
					<p v-for="(trace, idx) in lycheeError.trace" :key="'trace' + idx">
						{{ trace.file + ":" + trace.line }} &mdash; {{ trace.function }}
					</p>
				</UCard>
			</div>
			<div v-else-if="lycheeError.message">
				<UAlert color="error" :title="lycheeError.message" @click="closeError" />
			</div>
			<div v-else v-html="lycheeError"></div>
		</template>
		<div v-if="jsError !== null" class="w-full h-full absolute top-0 left-0 bg-default z-50">
			<UAlert color="error" :title="`${jsError.message} in ${jsError.filename}:${jsError.lineno}`" @click="closeError" />
		</div>
	</template>
	<SessionExpiredReload v-model:open="sessionExpired" />
</template>
<script setup lang="ts">
import { ref } from "vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import SessionExpiredReload from "@/v8/components/modals/SessionExpiredReload.vue";

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
