<template>
<Card class="max-w-xl mx-auto">
	<template #content>
		<form>
			<div class="w-full mt-2">
				<div class="py-5">
					{{ $t('lychee.PASSWORD_TITLE') }}
				</div>
				<FloatLabel>
					<InputPassword id="oldPassword" v-model="oldPassword" autofocus />
					<label class="" for="oldPassword">{{ $t("lychee.USERNAME") }}</label>
				</FloatLabel>
			</div>
			<div class="w-full mt-2">
				<div class="py-5">
					{{ $t('lychee.PASSWORD_TEXT') }}
				</div>
				<FloatLabel>
					<InputText id="username" v-model="username" />
					<label class="" for="username">{{ $t("lychee.LOGIN_USERNAME") }}</label>
				</FloatLabel>
				<FloatLabel class="mt-4" >
					<InputPassword id="password" v-model="password" />
					<label class="" for="password">{{ $t("lychee.LOGIN_PASSWORD") }}</label>
				</FloatLabel>
				<FloatLabel class="mt-4">
					<InputPassword id="password_confirmation" v-model="password_confirmation" />
					<label class="" for="password_confirmation">{{ $t("lychee.LOGIN_PASSWORD_CONFIRM") }}</label>
				</FloatLabel>
			</div>
			<div class="flex w-full mt-4">
				<Button class="p-3 w-full font-bold border-none text-primary-500 hover:bg-primary-500 hover:text-surface-0 flex-shrink">{{ $t('lychee.PASSWORD_CHANGE') }}</Button>
				<Button class="p-3 w-full font-bold border-none text-primary-500 hover:bg-primary-500 hover:text-surface-0 flex-shrink" @click="isApiTokenOpen = !isApiTokenOpen">{{ $t('lychee.TOKEN_BUTTON') }}</Button>
			</div>
		</form>
	</template>
</Card>
<ApiToken v-model="isApiTokenOpen" />
</template>
<script setup lang="ts">
import Card from 'primevue/card';
import { ref } from 'vue';
import InputPassword from '@/components/forms/basic/InputPassword.vue';
import InputText from '@/components/forms/basic/InputText.vue';
import FloatLabel from 'primevue/floatlabel';
import Button from 'primevue/button';
import ApiToken from './ApiToken.vue';

const isApiTokenOpen = ref(false);
const oldPassword = ref(undefined as undefined | string);
const username = ref(undefined as undefined | string);
const password = ref(undefined as undefined | string);
const password_confirmation = ref(undefined as undefined | string);

</script>