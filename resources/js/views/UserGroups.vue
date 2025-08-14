<template>
	<AddUserGroupModal
		v-if="allUsers !== undefined"
		v-model:visible="visible"
		v-model:description="selectedGroupDescription"
		v-model:name="selectedGroupName"
		v-model:group-id="selectedGroupId"
		:user-list="allUsers"
		@refresh="fetchUserGroups"
	/>
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ $t("user-groups.title") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel class="max-w-3xl mx-auto border-0">
		<div v-if="can_create_user_groups" class="w-full">
			<p class="text-muted-color-emphasis">{{ $t("user-groups.explanation") }}</p>
			<div class="flex justify-end mt-8 mb-8">
				<Button severity="primary" class="border-none p-3" @click="create">{{ $t("user-groups.create_group") }}</Button>
			</div>
		</div>
		<div v-if="userGroups === undefined">
			<div class="text-center text-muted-color-emphasis mt-4">
				{{ $t("user-groups.loading") }}
			</div>
		</div>
		<div v-else-if="userGroups.length === 0" class="text-center text-muted-color-emphasis mt-4">
			{{ $t("user-groups.empty") }}
		</div>
		<template v-else>
			<div
				v-for="(group, idx) in userGroups"
				:key="`G${group.id}`"
				:class="{
					'text-left text-muted-color-emphasis my-8 relative border-surface-400  pt-4': true,
					'border-t': idx > 0,
				}"
			>
				<div class="flex justify-between items-start">
					<div>
						<h2 class="text-xl font-bold capitalize">{{ group.name }}</h2>
						<p class="text-muted-color">{{ group.description }}</p>
					</div>
					<div class="flex items-center relative">
						<Button
							v-if="group.rights.can_edit"
							text
							severity="primary"
							:label="$t('user-groups.edit')"
							icon="pi pi-pencil"
							class="border-none rounded-none rounded-l-xl py-1"
							@click="edit(group)"
						/>
						<Button
							v-if="can_create_user_groups"
							:id="`delete-group-${group.id}`"
							text
							severity="danger"
							:label="$t('user-groups.delete')"
							icon="pi pi-trash"
							class="border-none rounded-none rounded-r-xl py-1"
							@click="confirmDelete(group)"
						/>
						<Select
							v-if="group.rights.can_manage"
							v-model="selectedUserToAdd"
							:options="availableUsers(group)"
							option-label="username"
							:placeholder="$t('user-groups.add_member')"
							class="w-56 mr-2"
							@update:model-value="addMemberToGroup(group)"
						/>
					</div>
				</div>
				<div v-if="group.members.length > 0" class="flex flex-wrap gap-y-1 gap-x-4 mt-3">
					<span v-for="member in group.members" :key="`G${group.id}:${member.id}`" class="flex items-center hover:text-color-emphasis">
						<button class="mr-1 cursor-pointer" @click="editRole(group, member.id, member.role)">
							<i v-if="member.role === 'admin'" class="pi pi-crown text-orange-400 mr-1" />
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
					{{ $t("user-groups.empty_group") }}
				</div>
				<div v-else>
					<i class="pi pi-exclamation-triangle text-orange-500 mr-2" />
					{{ $t("user-groups.no_permission_members") }}
				</div>
			</div>
		</template>
	</Panel>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { UserGroupService } from "@/services/user-group-service";
import Toolbar from "primevue/toolbar";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import Panel from "primevue/panel";
import AddUserGroupModal from "@/components/forms/group/AddUserGroupModal.vue";
import Button from "primevue/button";
import UsersService from "@/services/users-service";
import Select from "primevue/select";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";

const toast = useToast();

const can_create_user_groups = ref<boolean>(false);
const userGroups = ref<App.Http.Resources.Models.UserGroupResource[] | undefined>(undefined);
const visible = ref(false);

const selectedGroupId = ref<number | undefined>(undefined);
const selectedGroupName = ref<string | undefined>(undefined);
const selectedGroupDescription = ref<string | undefined>(undefined);

const selectedUserToAdd = ref<App.Http.Resources.Models.LightUserResource | undefined>(undefined);
const allUsers = ref<App.Http.Resources.Models.LightUserResource[] | undefined>(undefined);

const confirm = useConfirm();
function confirmDelete(group: App.Http.Resources.Models.UserGroupResource) {
	if (can_create_user_groups.value === false) {
		toast.add({
			severity: "error",
			summary: trans("toasts.error"),
			detail: trans("user-groups.no_permission_delete"),
			life: 3000,
		});
		return;
	}

	confirm.require({
		header: trans("user-groups.delete_group_header"),
		message: trans("user-groups.delete_group_confirm"),
		icon: "pi pi-exclamation-triangle before:text-orange-500",
		rejectProps: {
			label: trans("user-groups.cancel"),
			severity: "secondary",
			class: "border-none",
			outlined: true,
		},
		acceptProps: {
			label: trans("user-groups.delete"),
			severity: "danger",
			class: "border-none",
		},
		accept: () => {
			UserGroupService.deleteUserGroup(group.id).then(fetchUserGroups);
		},
	});
}

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
			summary: trans("toasts.success"),
			detail: sprintf(trans("user-groups.user_removed"), member.username, group.name),
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
