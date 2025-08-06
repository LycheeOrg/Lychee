<template>
	<Fieldset :legend="title" :toggleable="true" class="mb-4 hover:border-primary-500 pt-2 max-w-xl mx-auto" v-if="oauths !== undefined">
		<div class="pt-5" v-if="oauths.length === 0">
			<p class="text-muted-color">{{ $t("profile.oauth.setup_env") }}</p>
		</div>
		<template v-else>
			<div
				v-for="oauth in oauths"
				class=" text-muted-color-emphasis {{ $oauthData->isEnabled ? '' : 'hover:text-primary-400'}}"
				:key="oauth.providerType"
			>
				<i class="align-middle w-4" :class="oauth.icon + (oauth.isEnabled ? 'text-create-600' : '')"></i>
				<span class="ltr:ml-2 rtl:mr-2" v-if="oauth.isEnabled">
					{{ sprintf($t("profile.oauth.token_registered"), capitalize(oauth.providerType)) }}
					<a @click="clear(oauth.providerType)" class="ltr:ml-2 rtl:mr-2 cursor-pointer italic text-muted-color hover:text-danger-800">
						({{ $t("profile.oauth.reset") }})
					</a>
				</span>
				<a v-else :href="oauth.registrationRoute" class="ltr:ml-2 rtl:mr-2 cursor-pointer">
					{{ sprintf($t("profile.oauth.setup"), capitalize(oauth.providerType)) }}
				</a>
			</div>
		</template>
	</Fieldset>
</template>
<script setup lang="ts">
import OauthService from "@/services/oauth-service";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import { computed, ref } from "vue";
import Fieldset from "@/components/forms/basic/Fieldset.vue";

type OauthData = {
	providerType: string;
	isEnabled: boolean;
	registrationRoute: string;
	icon: string;
};

const oauths = ref<OauthData[] | undefined>(undefined);
const title = computed(() => {
	if (oauths.value?.length === 0) {
		return trans("profile.oauth.header_not_available");
	}

	return trans("profile.oauth.header");
});

function refresh() {
	OauthService.list().then((response) => {
		oauths.value = (response.data as App.Http.Resources.Oauth.OauthRegistrationData[]).map(mapToOauthData);
	});
}

function mapToOauthData(data: App.Http.Resources.Oauth.OauthRegistrationData): OauthData {
	return {
		providerType: data.provider_type,
		isEnabled: data.is_enabled,
		registrationRoute: data.registration_route,
		icon: OauthService.providerIcon(data.provider_type),
	};
}

function capitalize(s: string): string {
	return s.charAt(0).toUpperCase() + s.slice(1);
}

function clear(providerType: string) {
	OauthService.clear(providerType).then(() => {
		refresh();
	});
}

refresh();
</script>
