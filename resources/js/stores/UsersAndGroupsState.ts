import { UserGroupService } from "@/services/user-group-service";
import UsersService from "@/services/users-service";
import { defineStore } from "pinia";
import { useLycheeStateStore } from "./LycheeState";

export type UsersAndGroupStore = ReturnType<typeof useUsersAndGroupStore>;

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

export const useUsersAndGroupStore = defineStore("users-and-groups-store", {
	state: () => ({
		isLoading: false,
		usersGroupsList: undefined as UserOrGroup[] | undefined,
	}),
	actions: {
		reset() {
			this.isLoading = false;
			this.usersGroupsList = undefined;
		},
		async load(): Promise<void> {
			if (this.isLoading) {
				return Promise.resolve();
			}
			if (this.usersGroupsList !== undefined) {
				return Promise.resolve();
			}
			this.usersGroupsList = [];
			return Promise.allSettled([this._loadUsers(), this._loadGroups()]).then(() => {});
		},
		_loadUsers(): Promise<void> {
			return UsersService.get().then((response) => {
				if (response.data.length === 0) {
					return;
				}

				response.data.forEach((user) => {
					this.usersGroupsList?.push({ id: user.id, name: user.username, type: "user" as const });
				});
			});
		},
		_loadGroups(): Promise<void> {
			const isSupporter = useLycheeStateStore().is_se_enabled;
			if (!isSupporter) {
				return Promise.resolve();
			}

			return UserGroupService.listUserGroups().then((response) => {
				if (response.data.user_groups.length === 0) {
					return;
				}

				response.data.user_groups.forEach((group) => {
					this.usersGroupsList?.push({
						id: group.id,
						name: group.name,
						type: "group" as const,
					});
				});
			});
		},
	},
});
