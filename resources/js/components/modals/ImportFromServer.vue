<template>
	<Dialog
		v-model:visible="visible"
		modal
		:pt="{
			root: '!border-none',
		}"
		@hide="closeCallback"
	>
		<template #container="{ closeCallback }">
			<div class="flex flex-col gap-4 bg-gradient-to-b from-bg-300 to-bg-400 relative max-w-full rounded-md text-muted-color">
				<div class="p-9">
					<p class="mb-5 text-base text-muted-color-emphasis">
						This functionality is no longer available (since version 5)<br />for the following reasons:
					</p>
					<ul class="pl-5">
						<li class="list-decimal list-outside">Long process required to keep the browser window open.</li>
						<li class="list-decimal list-outside">Sessions time-out were breaking the import.</li>
						<li class="list-decimal list-outside">
							A more efficient command line alternative is available:<br />
							<pre class="inline-block font-mono text-muted-color-emphasis text-sm">php artisan lychee:sync</pre>
						</li>
					</ul>
				</div>
				<div class="flex justify-center">
					<Button @click="closeCallback" text autofocus class="p-3 w-full font-bold border-1 border-white-alpha-30 hover:bg-white-alpha-10">
						{{ $t("lychee.CLOSE") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { ref, watch } from "vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";

const props = defineProps<{
	visible: boolean;
}>();
const visible = ref(props.visible);

const emit = defineEmits(["close"]);

watch(
	() => props.visible,
	(value) => {
		visible.value = value;
	},
);

function closeCallback() {
	visible.value = false;
	emit("close");
}
</script>
