<template>
	<Select
		id="targetUser"
		v-model="selectedTarget"
		class="w-full border-none"
		filter
		:placeholder="$t('dialogs.target_user.placeholder')"
		:loading="options === undefined"
		:options="options"
		option-label="name"
		show-clear
		@update:model-value="selected"
	>
		<template #value="slotProps">
			<div v-if="slotProps.value" class="flex items-center">
				<div>
					<i v-if="slotProps.value.type === 'group'" class="pi pi-users ltr:mr-1 rtl:ml-1" />
					{{ $t(slotProps.value.name) }}
				</div>
			</div>
		</template>
		<template #option="slotProps">
			<div class="flex items-center">
				<span>
					<i v-if="slotProps.option.type === 'group'" class="pi pi-users ltr:mr-1 rtl:ml-1" />
					{{ slotProps.option.name }}
				</span>
			</div>
		</template>
	</Select>
</template>
<script setup lang="ts">
import { onMounted, ref, watch } from "vue";
import Select from "primevue/select";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { type UserOrGroup, type UserOrGroupId, useUsersAndGroupStore } from "@/stores/UsersAndGroupsState";

const lycheeStore = useLycheeStateStore();
const usersAndGroupsStore = useUsersAndGroupStore();

const props = defineProps<{
	filteredUsersIds?: UserOrGroupId[];
	withGroups?: boolean;
}>();

const emits = defineEmits<{
	selected: [user: UserOrGroup];
	"no-target": [];
}>();

const options = ref<UserOrGroup[] | undefined>(undefined);
const selectedTarget = ref<UserOrGroup | undefined>(undefined);

function selected() {
	if (selectedTarget.value === undefined) {
		return;
	}

	if (emits !== undefined) {
		emits("selected", selectedTarget.value);
	}
}

function filterUserGroups(filteredUsersIds: UserOrGroupId[] = []) {
	if (usersAndGroupsStore.usersGroupsList === undefined || usersAndGroupsStore.usersGroupsList.length === 0) {
		return;
	}
	const userIds = filteredUsersIds.filter((user) => user.type === "user").map((user) => user.id);
	const groupIds = filteredUsersIds.filter((group) => group.type === "group").map((group) => group.id);

	options.value = usersAndGroupsStore.usersGroupsList.filter(
		(userGroup) =>
			(userGroup.type === "user" && !userIds.includes(userGroup.id)) ||
			(props.withGroups && userGroup.type === "group" && !groupIds.includes(userGroup.id)),
	);

	if (options.value.length === 0 && emits !== undefined) {
		emits("no-target");
	}
}

onMounted(async () => {
	await lycheeStore.load();
	await usersAndGroupsStore.load();
	filterUserGroups(props.filteredUsersIds);
});

watch(
	() => props.filteredUsersIds,
	(v) => filterUserGroups(v),
);
</script>
