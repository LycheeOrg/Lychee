<template>
	<UCard class="p-9 mx-auto mt-16 max-w-3xl" :ui="{ header: 'hidden', body: 'flex flex-col items-center' }">
		<div class="my-12">
			<img v-if="site_logo !== ''" :src="site_logo" alt="logo" class="max-h-24 max-w-xs object-contain mx-auto" />
			<h1 v-else class="text-center text-2xl text-highlighted uppercase font-extralight">
				{{ title }}
			</h1>
		</div>
		<form class="flex flex-col gap-4 max-w-md w-full text-sm" @submit.prevent="unlock">
			<p class="text-center text-muted">{{ $t("dialogs.gallery_unlock.password_required") }}</p>
			<UFormField :label="$t('dialogs.gallery_unlock.password')">
				<InputPassword
					id="gallery_password"
					v-model="password"
					autocomplete="current-password"
					:autofocus="true"
					:invalid="error !== undefined"
				/>
				<UAlert v-if="error !== undefined" color="error" variant="soft" class="mt-2" :description="$t(`dialogs.gallery_unlock.${error}`)" />
			</UFormField>
			<UButton
				type="submit"
				color="primary"
				variant="solid"
				class="justify-center font-bold mt-4"
				:loading="is_submitting"
				:disabled="password === ''"
			>
				{{ $t("dialogs.gallery_unlock.unlock") }}
			</UButton>
		</form>
		<router-link :to="{ name: 'login' }" class="text-highlighted text-sm font-bold hover:underline mt-6">
			{{ $t("dialogs.gallery_unlock.sign_in") }}
		</router-link>
	</UCard>
</template>
<script setup lang="ts">
import InputPassword from "@/v8/components/forms/basic/InputPassword.vue";
import { useGalleryUnlock } from "@/composables/useGalleryUnlock";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const { site_logo, title } = storeToRefs(useLycheeStateStore());
const { password, error, is_submitting, unlock } = useGalleryUnlock();
</script>
