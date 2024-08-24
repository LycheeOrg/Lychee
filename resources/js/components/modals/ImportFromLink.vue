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
					<p class="mb-5 text-muted-color-emphasis text-base">{{ $t("lychee.UPLOAD_IMPORT_INSTR") }}</p>
					<form>
						<div class="my-3 first:mt-0 last:mb-0">
							<Textarea
								id="links"
								class="w-full h-48 p-3 w-full border-t-transparent border-r-transparent border-b border-l hover:border-b-primary-400 hover:border-l-primary-400 focus:border-b-primary-400 focus:border-l-primary-400"
								v-model="urls"
								rows="5"
								cols="30"
								placeholder="https://&#10;https://&#10;..."
							/>
						</div>
					</form>
				</div>
				<div class="flex justify-center">
					<Button
						text
						class="p-3 w-full font-bold border-none text-muted-color hover:text-danger-700 rounded-bl-xl"
						@click="closeCallback"
						>{{ $t("lychee.CANCEL") }}</Button
					>
					<Button
						text
						class="p-3 w-full font-bold border-none text-primary-500 hover:bg-primary-500 hover:text-surface-0 rounded-none rounded-br-xl"
						@click="submit"
						>{{ $t("lychee.UPLOAD_IMPORT") }}</Button
					>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { ref, Ref } from "vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import PhotoService from "@/services/photo-service";
import Textarea from "primevue/textarea";

const visible = defineModel("visible", { default: false }) as Ref<boolean>;
const props = defineProps<{ parentId: string | null }>();
const emit = defineEmits<{ (e: "refresh"): void }>();

const urls = ref<string>("");

function submit() {
	PhotoService.importFromUrl(urls.value.split("\n"), props.parentId).then(() => {
		urls.value = "";
		visible.value = false;
		emit("refresh");
	});
}

function closeCallback() {
	visible.value = false;
}
</script>
