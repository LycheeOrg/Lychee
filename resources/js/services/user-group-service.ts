import axios, { AxiosResponse } from "axios";
import Constants from "./constants";

export const UserGroupService = {
	/**
	 * Fetch all user groups.
	 */
	listUserGroups(): Promise<AxiosResponse<App.Http.Resources.Collections.UserGroupDataResource>> {
		return axios.get(`${Constants.getApiUrl()}UserGroups`, { data: {} });
	},

	/**
	 * Create a new user group.
	 * @param name - The name of the user group.
	 * @param description - The description of the user group.
	 */
	createUserGroup(name: string, description: string): Promise<AxiosResponse<App.Http.Resources.Models.UserGroupResource>> {
		return axios.post(`${Constants.getApiUrl()}UserGroups`, { name: name, description: description });
	},

	/**
	 * Update an existing user group.
	 * @param groupId - The ID of the user group.
	 * @param name - The new name of the user group.
	 * @param description - The new description of the user group.
	 */
	updateUserGroup(groupId: number, name: string, description: string): Promise<AxiosResponse<App.Http.Resources.Models.UserGroupResource>> {
		return axios.patch(`${Constants.getApiUrl()}UserGroups/`, { group_id: groupId, name: name, description: description });
	},

	/**
	 * Delete a user group.
	 * @param groupId - The ID of the user group.
	 */
	deleteUserGroup(groupId: number): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}UserGroups/`, { data: { group_id: groupId } });
	},

	/**
	 * Get the details of a user group.
	 */
	getUserGroup(groupId: number): Promise<AxiosResponse<App.Http.Resources.Models.UserGroupResource>> {
		return axios.get(`${Constants.getApiUrl()}UserGroups/`, { data: { group_id: groupId } });
	},

	/**
	 * Add a user to a user group.
	 * @param groupId - The ID of the user group.
	 * @param userId - The ID of the user to add.
	 * @param role - The role of the user in the group (e.g., 'member' or 'admin').
	 */
	addUserToGroup(groupId: number, userId: number, role: App.Enum.UserGroupRole): Promise<AxiosResponse<void>> {
		return axios.post(`${Constants.getApiUrl()}UserGroups/Users`, { group_id: groupId, user_id: userId, role: role });
	},

	/**
	 * Remove a user from a user group.
	 * @param groupId - The ID of the user group.
	 * @param userId - The ID of the user to remove.
	 */
	removeUserFromGroup(groupId: number, userId: number): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}UserGroups/Users/`, { data: { group_id: groupId, user_id: userId } });
	},

	/**
	 * Update the role of a user in a user group.
	 * @param groupId - The ID of the user group.
	 * @param userId - The ID of the user.
	 * @param role - The new role of the user in the group (e.g., 'member' or 'admin').
	 */
	updateUserRole(groupId: number, userId: number, role: string): Promise<AxiosResponse<void>> {
		return axios.patch(`${Constants.getApiUrl()}UserGroups/Users/`, { group_id: groupId, user_id: userId, role: role });
	},
};
