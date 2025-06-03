<template>
	<AddUserGroupModal
		v-if="allUsers !== undefined"
		:user-list="allUsers"
		v-model:visible="visible"
		v-model:description="selectedGroupDescription"
		v-model:name="selectedGroupName"
		v-model:group-id="selectedGroupId"
		@refresh="fetchUserGroups"
	/>
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ "User Groups" }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel class="max-w-3xl mx-auto border-0">
		<div class="w-full" v-if="can_create_user_groups">
			<p class="text-muted-color-emphasis">{{ $t("user-groups.explanation") }}</p>
			<div class="flex justify-end mt-8 mb-8">
				<Button severity="primary" class="border-none p-3" @click="create">{{ $t("user-groups.create") }}</Button>
			</div>
		</div>
		<div v-if="userGroups === undefined">
			<div class="text-center text-muted-color-emphasis mt-4">
				{{ "Loading user groups..." }}
			</div>
		</div>
		<div v-else-if="userGroups.length === 0" class="text-center text-muted-color-emphasis mt-4">
			{{ "No user groups found." }}
		</div>
		<template v-else>
			<div
				v-for="(group, idx) in userGroups"
				:class="{
					'text-left text-muted-color-emphasis my-8 relative border-surface-400  pt-4': true,
					'border-t': idx > 0,
				}"
				:key="`G${group.id}`"
			>
				<div class="flex justify-between items-start">
					<div>
						<h2 class="text-xl font-bold capitalize">{{ group.name }}</h2>
						<p class="text-muted-color">{{ group.description }}</p>
					</div>
					<div class="flex items-center">
						<Button
							v-if="group.rights.can_edit"
							text
							severity="primary"
							label="Edit"
							icon="pi pi-pencil"
							class="border-none rounded-none rounded-l-xl py-1"
							@click="edit(group)"
						/>
						<Button
							v-if="can_create_user_groups"
							text
							severity="danger"
							label="Delete"
							icon="pi pi-trash"
							class="border-none rounded-none rounded-r-xl py-1"
							@click="UserGroupService.deleteUserGroup(group.id).then(fetchUserGroups)"
						/>
						<Select
							v-if="group.rights.can_manage"
							v-model="selectedUserToAdd"
							:options="availableUsers(group)"
							optionLabel="username"
							placeholder="Add member..."
							class="w-56 mr-2"
							@update:model-value="addMemberToGroup(group)"
						/>
					</div>
				</div>
				<div class="flex flex-wrap gap-y-1 gap-x-4 mt-3" v-if="group.members.length > 0">
					<span v-for="member in group.members" :key="`G${group.id}:${member.id}`" class="flex items-center hover:text-color-emphasis">
						<button class="mr-1 cursor-pointer" @click="editRole(group, member.id, member.role)">
							<i class="pi pi-crown text-orange-400 mr-1" v-if="member.role === 'admin'" />
							{{ member.username }}
						</button>
						<button
							v-if="group.rights.can_manage"
							:class="{
								'pi pi-times cursor-pointer': true,
								'border-surface-400 rounded-full inline-block border p-0.5 text-3xs': true,
								'hover:border-danger-700 hover:text-danger-700': true,
							}"
							@click="deleteMember(group, member)"
						/>
					</span>
				</div>
				<div v-else-if="group.rights.can_manage" class="text-muted-color italic mt-2">
					{{ "This group is empty." }}
				</div>
				<div v-else>
					<i class="pi pi-exclamation-triangle text-orange-500 mr-2" />
					{{ "You do not have the permission to see the members of this group." }}
				</div>
			</div>
		</template>
	</Panel>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { UserGroupService } from "../services/user-group-service";
import Toolbar from "primevue/toolbar";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import Panel from "primevue/panel";
import AddUserGroupModal from "@/components/forms/group/AddUserGroupModal.vue";
import Button from "primevue/button";
import UsersService from "@/services/users-service";
import Select from "primevue/select";
import { useToast } from "primevue/usetoast";

const toast = useToast();

const can_create_user_groups = ref<boolean>(false);
const userGroups = ref<App.Http.Resources.Models.UserGroupResource[] | undefined>(undefined);
const visible = ref(false);

const selectedGroupId = ref<number | undefined>(undefined);
const selectedGroupName = ref<string | undefined>(undefined);
const selectedGroupDescription = ref<string | undefined>(undefined);

const selectedUserToAdd = ref<App.Http.Resources.Models.LightUserResource | undefined>(undefined);
const allUsers = ref<App.Http.Resources.Models.LightUserResource[] | undefined>(undefined);

function fetchUserGroups() {
	UserGroupService.listUserGroups().then((response) => {
		can_create_user_groups.value = response.data.can_create_delete_user_groups;
		userGroups.value = response.data.user_groups;
	});
}

function fetchAllUsers() {
	UsersService.get().then((response) => {
		allUsers.value = response.data;
	});
}

function deleteMember(group: App.Http.Resources.Models.UserGroupResource, member: App.Http.Resources.Models.UserMemberGroupResource) {
	UserGroupService.removeUserFromGroup(group.id, member.id).then(() => {
		toast.add({
			severity: "success",
			summary: "Success",
			detail: `User ${member.username} removed from group ${group.name}`,
			life: 3000,
		});
		fetchUserGroups();
	});
}

function editRole(group: App.Http.Resources.Models.UserGroupResource, memberId: number, currentRole: string) {
	if (group.rights.can_manage === false) {
		return;
	}

	const newRole = currentRole === "admin" ? "member" : "admin";
	UserGroupService.updateUserRole(group.id, memberId, newRole).then(() => {
		fetchUserGroups();
	});
}

function availableUsers(group: App.Http.Resources.Models.UserGroupResource) {
	if (allUsers.value === undefined) {
		return [];
	}
	if (allUsers.value.length === 0) {
		return [];
	}

	const memberIds = group.members.map((m: App.Http.Resources.Models.UserMemberGroupResource) => m.id);
	return allUsers.value.filter((u) => !memberIds.includes(u.id));
}

function addMemberToGroup(group: App.Http.Resources.Models.UserGroupResource) {
	if (selectedUserToAdd.value === undefined) {
		return;
	}

	UserGroupService.addUserToGroup(group.id, selectedUserToAdd.value.id, "member").then(() => {
		selectedUserToAdd.value = undefined;
		fetchUserGroups();
	});
}

function create() {
	selectedGroupId.value = undefined;
	selectedGroupName.value = undefined;
	selectedGroupDescription.value = undefined;
	visible.value = true;
}

function edit(group: App.Http.Resources.Models.UserGroupResource) {
	selectedGroupId.value = group.id;
	selectedGroupName.value = group.name;
	selectedGroupDescription.value = group.description;
	visible.value = true;
}

onMounted(() => {
	fetchUserGroups();
	fetchAllUsers();
});
</script>
