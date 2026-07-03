<template>
	<Fieldset :legend="$t('settings.cssjs.header')" :toggleable="false" :collapsed="false">
		<div class="flex flex-col gap-4">
			<div>
				<UTextarea v-model="css" class="w-full h-48" :rows="10" />
				<UButton color="primary" class="w-full justify-center font-bold" @click="saveCss">{{ $t("settings.cssjs.change_css") }}</UButton>
			</div>
			<div>
				<UTextarea v-model="js" class="w-full h-48" :rows="10" />
				<UButton color="primary" class="w-full justify-center font-bold" @click="saveJs">{{ $t("settings.cssjs.change_js") }}</UButton>
			</div>
		</div>
	</Fieldset>
</template>
<script setup lang="ts">
import SettingsService from "@/services/settings-service";
import { trans } from "laravel-vue-i18n";
import { useAppToast } from "@/v8/composables/useAppToast";
import { onMounted } from "vue";
import { ref } from "vue";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";

const toast = useAppToast();

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
