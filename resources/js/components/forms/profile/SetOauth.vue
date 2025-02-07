<template>
	<Fieldset
		:legend="title"
		:toggleable="true"
		class="border-b-0 border-r-0 rounded-r-none rounded-b-none mb-4 hover:border-primary-500 pt-2 max-w-xl mx-auto"
		:pt:legendlabel:class="'capitalize'"
		v-if="oauths !== undefined"
	>
		<div class="pt-5" v-if="oauths.length === 0">
			<p>{{ $t("profile.oauth.setup_env") }}</p>
		</div>
		<template v-else>
			<div v-for="oauth in oauths" class=" text-muted-color-emphasis {{ $oauthData->isEnabled ? '' : 'hover:text-primary-400'}}">
				<i class="align-middle w-4" :class="oauth.icon + (oauth.isEnabled ? 'text-create-600' : '')"></i>
				<span class="ml-2" v-if="oauth.isEnabled">
					{{ sprintf($t("profile.oauth.token_registered"), capitalize(oauth.providerType)) }}
					<a @click="clear(oauth.providerType)" class="ml-2 cursor-pointer italic text-muted-color hover:text-danger-800"
						>({{ $t("profile.oauth.reset") }})</a
					>
				</span>
				<a v-else :href="oauth.registrationRoute" class="ml-2 cursor-pointer">
					{{ sprintf($t("profile.oauth.setup"), capitalize(oauth.providerType)) }}
				</a>
			</div>
		</template>
	</Fieldset>
</template>
<script setup lang="ts">
import OauthService from "@/services/oauth-service";
import { trans } from "laravel-vue-i18n";
import Fieldset from "primevue/fieldset";
import { sprintf } from "sprintf-js";
import { computed, ref } from "vue";

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
		providerType: data.providerType,
		isEnabled: data.isEnabled,
		registrationRoute: data.registrationRoute,
		icon: OauthService.providerIcon(data.providerType),
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
