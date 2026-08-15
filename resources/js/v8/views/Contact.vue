<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ contactConfig?.header ? contactConfig.header : $t("contact.title") }}
	</UHeader>

	<div class="max-w-2xl mx-auto mt-6">
		<ContactForm @loaded="onLoaded" />
	</div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import ContactForm from "@/v8/components/forms/contact/ContactForm.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useRouter } from "vue-router";

const lycheeStore = useLycheeStateStore();
lycheeStore.load();

const router = useRouter();
const contactConfig = ref<App.Http.Resources.GalleryConfigs.ContactConfig | undefined>(undefined);

function onLoaded(config: App.Http.Resources.GalleryConfigs.ContactConfig): void {
	contactConfig.value = config;
	if (!config.is_contact_form_enabled) {
		router.push("/gallery");
	}
}
</script>
