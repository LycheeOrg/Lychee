<template>
	<USelectMenu
		id="targetUser"
		v-model="selectMenuValue"
		class="w-full"
		:placeholder="$t('dialogs.target_user.placeholder')"
		:loading="options === undefined"
		:items="selectMenuItems"
		label-key="name"
		@update:model-value="selected"
	>
		<template #item-leading="{ item }">
			<UIcon v-if="(item as unknown as UserOrGroup).type === 'group'" name="lucide:users" />
		</template>
	</USelectMenu>
</template>
<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { type UserOrGroup, type UserOrGroupId, useUsersAndGroupStore } from "@/stores/UsersAndGroupsState";
import type { SelectMenuItem } from "@nuxt/ui";

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

// USelectMenu's generic item type reserves `type` for "label" | "item" | "separator";
// UserOrGroup's own `type` field ("user" | "group") collides structurally, so bind through
// an opaque cast rather than renaming the shared `UserOrGroup` type (used by v7 too).
const selectMenuItems = computed(() => options.value as unknown as SelectMenuItem[] | undefined);
const selectMenuValue = computed<SelectMenuItem | undefined>({
	get: () => selectedTarget.value as unknown as SelectMenuItem | undefined,
	set: (v) => {
		selectedTarget.value = v as unknown as UserOrGroup | undefined;
	},
});

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
