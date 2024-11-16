<template>
	<Select
		id="targetUser"
		class="w-full border-none"
		v-model="selectedTarget"
		@update:modelValue="selected"
		filter
		placeholder="Select user"
		:loading="options === undefined"
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
import { ref, watch } from "vue";
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
	selected: [user: App.Http.Resources.Models.LightUserResource];
	"no-target": [];
}>();

const options = ref<App.Http.Resources.Models.LightUserResource[] | undefined>(undefined);
const selectedTarget = ref<App.Http.Resources.Models.LightUserResource | undefined>(undefined);
const userList = ref<App.Http.Resources.Models.LightUserResource[] | undefined>(undefined);

function load() {
	UsersService.get().then((response) => {
		userList.value = response.data;
		filterUsers();
	});
}

load();

function selected() {
	if (selectedTarget.value === undefined) {
		return;
	}

	emits("selected", selectedTarget.value);
}

function filterUsers() {
	if (userList.value === undefined) {
		return;
	}

	options.value = userList.value.filter((user) => !props.filteredUsersIds.includes(user.id));
	if (options.value.length === 0) {
		emits("no-target");
	}
}

watch(() => props.filteredUsersIds, filterUsers);
</script>
