<template>
	<div>
		<div class="flex items-center justify-between gap-x-4 flex-wrap sm:flex-nowrap">
			<label
				:for="props.config.key"
				:class="{
					'w-full': true,
					'text-primary-emphasis': props.config.require_se,
					'text-muted-color-emphasis': !props.config.require_se,
				}"
				v-html="tDoc(props.config)"
			/>
			<div class="w-full grow flex items-center gap-2">
				<Tag
					:severity="isSet ? 'success' : 'secondary'"
					:value="isSet ? $t('settings.password_field.set') : $t('settings.password_field.not_set')"
				/>
				<InputPassword
					:id="props.config.key"
					v-model="val"
					class="py-1!"
					autocomplete="new-password"
					:placeholder="$t('settings.password_field.placeholder')"
					@update:model-value="update"
				/>
				<Button
					severity="secondary"
					size="small"
					class="border-none"
					:disabled="!isSet"
					:label="$t('settings.password_field.clear')"
					@click="clear"
				/>
			</div>
		</div>
		<div v-if="props.config.details" class="text-muted-color text-sm hidden sm:block" v-html="tDetails(props.config)" />
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import Tag from "primevue/tag";
import Button from "primevue/button";
import InputPassword from "@/v7/components/forms/basic/InputPassword.vue";
import { useTranslation } from "@/composables/useTranslation";

const { tDoc, tDetails } = useTranslation();

const props = defineProps<{
	config: App.Http.Resources.Models.ConfigResource;
}>();

// The stored value never reaches the client: the field starts empty, and `is_set` says whether a password exists.
const val = ref<string | null | undefined>("");
const is_cleared = ref(false);
const isSet = computed(() => props.config.is_set && !is_cleared.value);

const emits = defineEmits<{
	filled: [key: string, value: string];
	reset: [key: string];
}>();

// Emptying the field cancels the change: only the Clear button removes the password.
function update() {
	is_cleared.value = false;
	if (val.value === "" || val.value === null || val.value === undefined) {
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
