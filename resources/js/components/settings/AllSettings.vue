<template>
	<div v-if="configs">
		<Fieldset
			v-for="(configGroup, key, index) in configs.configs"
			:legend="key"
			:toggleable="true"
			class="border-b-0 border-r-0 rounded-r-none rounded-b-none mb-4 hover:border-primary-500 pt-2"
		>
			<div class="flex flex-col gap-4">
				<template v-for="config in configGroup">
					<VersionField v-if="config.key === 'version'" :config="config" />
					<StringField v-else-if="config.type.startsWith('string')" :config="config" />
					<BoolField v-else-if="config.type === '0|1'" :config="config" />
					<NumberField v-else-if="config.type === 'int'" :config="config" :min="0" />
					<NumberField v-else-if="config.type === 'positive'" :config="config" :min="1" />
					<p v-else>{{ config.key }} -- {{ config.value }} -- {{ config.documentation }} -- {{ config.type }}</p>
				</template>
			</div>
		</Fieldset>
	</div>
</template>
<script setup lang="ts">
import SettingsService from "@/services/settings-service";
import { ref } from "vue";
import StringField from "@/components/forms/settings/StringField.vue";
import BoolField from "@/components/forms/settings/BoolField.vue";
import NumberField from "../forms/settings/NumberField.vue";
import VersionField from "../forms/settings/VersionField.vue";
import Fieldset from "primevue/fieldset";

const active = ref([] as string[]);
const configs = ref(undefined as undefined | App.Http.Resources.Collections.ConfigCollectionResource);

function load() {
	SettingsService.getAll().then((response) => {
		configs.value = response.data as App.Http.Resources.Collections.ConfigCollectionResource;
		active.value = [...Array(Object.keys(configs.value.configs).length).keys()].map((i: number) => i.toString());
	});
}

load();
</script>
