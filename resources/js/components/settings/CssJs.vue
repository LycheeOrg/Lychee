<template>
	<Fieldset
		:legend="$t('settings.cssjs.header')"
		class="border-b-0 border-r-0 rounded-r-none rounded-b-none"
		:toggleable="false"
		:collapsed="false"
	>
		<div class="flex flex-col gap-4">
			<div>
				<Textarea v-model="css" class="w-full h-48" rows="10" cols="30" />
				<Button severity="primary" class="w-full border-none font-bold" @click="saveCss">{{ $t("settings.cssjs.change_css") }}</Button>
			</div>
			<div>
				<Textarea v-model="js" class="w-full h-48" rows="10" cols="30" />
				<Button severity="primary" class="w-full border-none font-bold" @click="saveJs">{{ $t("settings.cssjs.change_js") }}</Button>
			</div>
		</div>
	</Fieldset>
</template>
<script setup lang="ts">
import SettingsService from "@/services/settings-service";
import { trans } from "laravel-vue-i18n";
import { useToast } from "primevue/usetoast";
import { onMounted } from "vue";
import { ref } from "vue";
import Textarea from "../forms/basic/Textarea.vue";
import Button from "primevue/button";
import Fieldset from "primevue/fieldset";

const toast = useToast();

const css = ref<string | undefined>(undefined);
const js = ref<string | undefined>(undefined);

function loadCssJs() {
	SettingsService.getCss()
		.then((response) => {
			css.value = response.data;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("settings.toasts.error"), detail: trans("settings.toasts.error_load_css"), life: 3000 });
		});

	SettingsService.getJs()
		.then((response) => {
			js.value = response.data;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("settings.toasts.error"), detail: trans("settings.toasts.error_load_js"), life: 3000 });
		});
}

function saveCss() {
	SettingsService.setCss(css.value ?? "")
		.then(() => {
			toast.add({ severity: "success", summary: trans("settings.toasts.change_saved"), life: 3000 });
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("settings.toasts.error"), detail: trans("settings.toasts.error_save_css"), life: 3000 });
		});
}

function saveJs() {
	SettingsService.setJs(js.value ?? "")
		.then(() => {
			toast.add({ severity: "success", summary: trans("settings.toasts.change_saved"), life: 3000 });
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("settings.toasts.error"), detail: trans("settings.toasts.error_save_js"), life: 3000 });
		});
}

onMounted(() => {
	loadCssJs();
});
</script>
