import { UserGroupService } from "@/services/user-group-service";
import UsersService from "@/services/users-service";
import { Ref, ref } from "vue";

export type User = {
	id: number;
	name: string;
	type: "user";
};

export type Group = {
	id: number;
	name: string;
	type: "group";
};

export type UserOrGroup = User | Group;
export type UserOrGroupId = { id: number; type: "user" | "group" };

export function useSearchUserGroupComputed(
	is_supporter: Ref<boolean>,
	emits: (((evt: "selected", userOrGroup: UserOrGroup) => void) & ((evt: "no-target") => void)) | undefined,
) {
	const options = ref<UserOrGroup[] | undefined>(undefined);
	const selectedTarget = ref<UserOrGroup | undefined>(undefined);
	const usersGroupsList = ref<UserOrGroup[] | undefined>(undefined);

	function loadUsers(): Promise<void> {
		return UsersService.get().then((response) => {
			if (response.data.length === 0) {
				return;
			}

			response.data.forEach((user) => {
				usersGroupsList.value?.push({ id: user.id, name: user.username, type: "user" as const });
			});
		});
	}

	function loadGroups(): Promise<void> {
		if (!is_supporter.value) {
			return Promise.resolve();
		}

		return UserGroupService.listUserGroups().then((response) => {
			if (response.data.user_groups.length === 0) {
				return;
			}

			response.data.user_groups.forEach((group) => {
				usersGroupsList.value?.push({
					id: group.id,
					name: group.name,
					type: "group" as const,
				});
			});
		});
	}

	function load() {
		usersGroupsList.value = [];

		Promise.all([loadUsers(), loadGroups()]).then(() => {
			filterUserGroups();
		});
	}

	function selected() {
		if (selectedTarget.value === undefined) {
			return;
		}

		if (emits !== undefined) {
			emits("selected", selectedTarget.value);
		}
	}

	function filterUserGroups(filteredUsersIds: UserOrGroupId[] = []) {
		if (usersGroupsList.value === undefined) {
			return;
		}
		const userIds = filteredUsersIds.filter((user) => user.type === "user").map((user) => user.id);
		const groupIds = filteredUsersIds.filter((group) => group.type === "group").map((group) => group.id);

		options.value = usersGroupsList.value.filter(
			(userGroup) =>
				(userGroup.type === "user" && !userIds.includes(userGroup.id)) || (userGroup.type === "group" && !groupIds.includes(userGroup.id)),
		);

		if (options.value.length === 0 && emits !== undefined) {
			emits("no-target");
		}
	}

	return {
		options,
		selectedTarget,
		usersGroupsList,
		load,
		selected,
		filterUserGroups,
	};
}
