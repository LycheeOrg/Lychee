<template>
	<Fieldset v-if="oauths !== undefined" :legend="title" :toggleable="true" class="hover:border-primary pt-2">
		<div v-if="oauths.length === 0" class="pt-5">
			<p class="text-muted">{{ $t("profile.oauth.setup_env") }}</p>
		</div>
		<template v-else>
			<div v-for="oauth in oauths" :key="oauth.providerType" class="text-highlighted">
				<UIcon :name="oauth.icon" class="align-middle" :class="oauth.isEnabled ? 'text-success' : ''" />
				<span v-if="oauth.isEnabled" class="ltr:ml-2 rtl:mr-2">
					{{ sprintf($t("profile.oauth.token_registered"), capitalize(oauth.providerType)) }}
					<a class="ltr:ml-2 rtl:mr-2 cursor-pointer italic text-muted hover:text-error" @click="clear(oauth.providerType)">
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
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";

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
