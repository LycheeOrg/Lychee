<template>
	<Panel class="border-none p-9 mx-auto mt-16 max-w-3xl" pt:content:class="flex flex-col items-center" pt:header:class="hidden">
		<div class="my-12">
			<img v-if="site_logo !== ''" :src="site_logo" alt="logo" class="max-h-24 max-w-xs object-contain mx-auto" />
			<h1 v-else class="text-center text-2xl text-surface-0 uppercase font-extralight">
				{{ title }}
			</h1>
		</div>
		<form class="flex flex-col gap-4 max-w-md w-full text-sm" @submit.prevent="unlock">
			<p class="text-center text-muted-color">{{ $t("dialogs.gallery_unlock.password_required") }}</p>
			<FloatLabel variant="on">
				<InputPassword id="gallery_password" v-model="password" autocomplete="current-password" autofocus :invalid="error !== undefined" />
				<label for="gallery_password">{{ $t("dialogs.gallery_unlock.password") }}</label>
			</FloatLabel>
			<Message v-if="error !== undefined" severity="error">{{ $t(`dialogs.gallery_unlock.${error}`) }}</Message>
			<Button type="submit" class="w-full font-bold border-none rounded-xl mt-4" :loading="is_submitting" :disabled="password === ''">
				{{ $t("dialogs.gallery_unlock.unlock") }}
			</Button>
		</form>
		<router-link :to="{ name: 'login' }" class="text-muted-color-emphasis text-sm font-bold hover:underline mt-6">
			{{ $t("dialogs.gallery_unlock.sign_in") }}
		</router-link>
	</Panel>
</template>
<script setup lang="ts">
import Panel from "primevue/panel";
import FloatLabel from "primevue/floatlabel";
import Message from "primevue/message";
import Button from "primevue/button";
import InputPassword from "@/v7/components/forms/basic/InputPassword.vue";
import { useGalleryUnlock } from "@/composables/useGalleryUnlock";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const { site_logo, title } = storeToRefs(useLycheeStateStore());
const { password, error, is_submitting, unlock } = useGalleryUnlock();
</script>
