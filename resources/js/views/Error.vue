<template>
	<div v-if="lycheeError !== null" class="w-full h-full absolute top-0 left-0 bg-panel z-50">
		<Message severity="error" @click="lycheeError = null">
			<span class="font-bold text-xl w-full" v-if="lycheeError.exception"
				>{{ lycheeError.exception }} in {{ lycheeError.file }}:{{ lycheeError.line }}</span
			>
			<span class="font-bold text-xl w-full" v-else>{{ lycheeError.message }}</span>
		</Message>
		<Panel>
			<template #header>
				<span class="font-bold text-xl">{{ lycheeError.message }}</span>
			</template>
			<Divider />
			<p v-for="trace in lycheeError.trace">{{ trace.file + ":" + trace.line }} &mdash; {{ trace.function }}</p>
		</Panel>
	</div>
	<div v-if="jsError !== null" class="w-full h-full absolute top-0 left-0 bg-panel z-50">
		<Message severity="error z-50" @click="jsError = null">
			<span class="font-bold text-xl">{{ jsError.message }} in {{ jsError.filename }}:{{ jsError.lineno }}</span>
		</Message>
	</div>
</template>
<script setup lang="ts">
import { type Ref, ref } from "vue";
import Divider from "primevue/divider";
import Message from "primevue/message";
import Panel from "primevue/panel";

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
};

const lycheeError = ref(null) as Ref<null | LycheeException>;
const jsError = ref(null) as Ref<null | ErrorEvent>;

window.addEventListener("error", function (e: Event) {
	console.log("error", e);
	// @ts-expect-error
	if (e.details !== undefined) {
		// @ts-expect-error
		lycheeError.value = e.detail;
		// @ts-expect-error
	} else if (e.detail !== undefined) {
		// @ts-expect-error
		lycheeError.value = e.detail;
	} else {
		// @ts-expect-error
		jsError.value = e as ErrorEvent;
		console.log(jsError.value);
	}
});
</script>

<style lang="css">
.bg-panel {
	background-color: var(--p-surface-0);
}

.lychee-dark .bg-panel {
	background-color: var(--p-surface-900);
}
</style>
