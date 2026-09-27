<template>
	<div class="flex flex-wrap items-center w-full">
		<label
			:for="props.config.key"
			class="w-1/2"
			:class="props.config.require_se ? 'text-primary' : 'text-highlighted'"
			v-html="tDoc(props.config)"
		/>
		<div class="w-1/2 flex items-center gap-2">
			<UBadge :color="isSet ? 'success' : 'neutral'" variant="soft">
				{{ isSet ? $t("settings.password_field.set") : $t("settings.password_field.not_set") }}
			</UBadge>
			<InputPassword
				:id="props.config.key"
				v-model="val"
				class="flex-1"
				autocomplete="new-password"
				:placeholder="$t('settings.password_field.placeholder')"
				@update:model-value="update"
			/>
			<UButton color="neutral" variant="soft" :disabled="!isSet" @click="clear">
				{{ $t("settings.password_field.clear") }}
			</UButton>
		</div>
		<div v-if="props.config.details" class="w-full text-muted text-sm" v-html="tDetails(props.config)" />
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import InputPassword from "@/v8/components/forms/basic/InputPassword.vue";
import { useTranslation } from "@/composables/useTranslation";

const { tDoc, tDetails } = useTranslation();

const props = defineProps<{
	config: App.Http.Resources.Models.ConfigResource;
}>();

// The stored value never reaches the client: the field starts empty, and `is_set` says whether a password exists.
const val = ref<string>("");
const is_cleared = ref(false);
const isSet = computed(() => props.config.is_set && !is_cleared.value);

const emits = defineEmits<{
	filled: [key: string, value: string];
	reset: [key: string];
}>();

// Emptying the field cancels the change: only the Clear button removes the password.
function update() {
	is_cleared.value = false;
	if (val.value === "") {
		emits("reset", props.config.key);
		return;
	}
	emits("filled", props.config.key, val.value);
}

function clear() {
	val.value = "";
	is_cleared.value = true;
	emits("filled", props.config.key, "");
}

// Saved or reloaded settings: back to an empty field.
watch(
	() => props.config,
	() => {
		val.value = "";
		is_cleared.value = false;
	},
);
</script>
