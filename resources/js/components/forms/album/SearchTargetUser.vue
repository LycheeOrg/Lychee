<template>
	<Select
		id="targetUser"
		class="w-full border-none"
		v-model="selectedTarget"
		@update:modelValue="selected"
		filter
		placeholder="Select user"
		:loading="options.length === 0"
		:options="options"
		optionLabel="username"
		showClear
	>
		<template #value="slotProps">
			<div v-if="slotProps.value" class="flex items-center">
				<div>{{ $t(slotProps.value.username) }}</div>
			</div>
		</template>
		<template #option="slotProps">
			<div class="flex items-center">
				<!-- <img :src="slotProps.option.thumb" alt="poster" class="w-4 rounded-sm" /> -->
				<span class="text-left">{{ slotProps.option.username }}</span>
			</div>
		</template>
	</Select>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Select from "primevue/select";
import UsersService from "@/services/users-service";

const props = withDefaults(
	defineProps<{
		filteredUsersIds?: number[];
	}>(),
	{
		filteredUsersIds: () => [],
	},
);
const emits = defineEmits<{
	(e: "selected", user: App.Http.Resources.Models.LightUserResource): void;
}>();

const options = ref([] as App.Http.Resources.Models.LightUserResource[]);
const selectedTarget = ref(undefined as App.Http.Resources.Models.LightUserResource | undefined);

console.log(props.filteredUsersIds);
function load() {
	UsersService.get().then((response) => {
		options.value = response.data.filter((user) => !props.filteredUsersIds.includes(user.id));
	});
}

load();

function selected() {
	if (selectedTarget.value === undefined) {
		return;
	}

	emits("selected", selectedTarget.value);
}
</script>
