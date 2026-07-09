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
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("user-groups.title") }}
	</UHeader>
	<div class="max-w-3xl mx-auto p-4">
		<div v-if="can_create_user_groups" class="w-full">
			<p class="text-highlighted">{{ $t("user-groups.explanation") }}</p>
			<div class="flex justify-end mt-8 mb-8">
				<UButton color="primary" @click="create">{{ $t("user-groups.create_group") }}</UButton>
			</div>
		</div>
		<div v-if="userGroups === undefined">
			<div class="text-center text-highlighted mt-4">
				{{ $t("user-groups.loading") }}
			</div>
		</div>
		<div v-else-if="userGroups.length === 0" class="text-center text-highlighted mt-4">
			{{ $t("user-groups.empty") }}
		</div>
		<template v-else>
			<div
				v-for="(group, idx) in userGroups"
				:key="`G${group.id}`"
				:class="{
					'text-left text-highlighted my-8 relative pt-4': true,
					'border-t border-accented': idx > 0,
				}"
			>
				<div class="flex justify-between items-start">
					<div>
						<h2 class="text-xl font-bold capitalize">{{ group.name }}</h2>
						<p class="text-muted">{{ group.description }}</p>
					</div>
					<div class="flex items-center gap-1 relative">
						<UButton
							v-if="group.rights.can_edit"
							variant="ghost"
							color="primary"
							:label="$t('user-groups.edit')"
							icon="prime:pencil"
							@click="edit(group)"
						/>
						<UButton
							v-if="can_create_user_groups"
							:id="`delete-group-${group.id}`"
							variant="ghost"
							color="error"
							:label="$t('user-groups.delete')"
							icon="prime:trash"
							@click="confirmDelete(group)"
						/>
						<USelectMenu
							v-if="group.rights.can_manage"
							v-model="selectedUserToAdd"
							:items="availableUsers(group)"
							label-key="username"
							:placeholder="$t('user-groups.add_member')"
							class="w-56"
							@update:model-value="addMemberToGroup(group)"
						/>
					</div>
				</div>
				<div v-if="group.members.length > 0" class="flex flex-wrap gap-y-1 gap-x-4 mt-3">
					<span v-for="member in group.members" :key="`G${group.id}:${member.id}`" class="flex items-center hover:text-highlighted">
						<button class="mr-1 cursor-pointer flex items-center gap-1" @click="editRole(group, member.id, member.role)">
							<UIcon v-if="member.role === 'admin'" name="prime:crown" class="text-orange-400" />
							{{ member.username }}
						</button>
						<button
							v-if="group.rights.can_manage"
							class="border-accented rounded-full inline-flex items-center justify-center border p-0.5 text-3xs hover:border-error hover:text-error"
							@click="deleteMember(group, member)"
						>
							<UIcon name="prime:times" />
						</button>
					</span>
				</div>
				<div v-else-if="group.rights.can_manage" class="text-muted italic mt-2">
					{{ $t("user-groups.empty_group") }}
				</div>
				<div v-else class="flex items-center gap-2">
					<UIcon name="prime:exclamation-triangle" class="text-orange-500" />
					{{ $t("user-groups.no_permission_members") }}
				</div>
			</div>
		</template>
	</div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { UserGroupService } from "@/services/user-group-service";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import AddUserGroupModal from "@/v8/components/forms/group/AddUserGroupModal.vue";
import UsersService from "@/services/users-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useConfirmDialog } from "@/v8/composables/useConfirmDialog";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";

const toast = useAppToast();

const can_create_user_groups = ref<boolean>(false);
const userGroups = ref<App.Http.Resources.Models.UserGroupResource[] | undefined>(undefined);
const visible = ref(false);

const selectedGroupId = ref<number | undefined>(undefined);
const selectedGroupName = ref<string | undefined>(undefined);
const selectedGroupDescription = ref<string | undefined>(undefined);

const selectedUserToAdd = ref<App.Http.Resources.Models.LightUserResource | undefined>(undefined);
const allUsers = ref<App.Http.Resources.Models.LightUserResource[] | undefined>(undefined);

const { confirm } = useConfirmDialog();
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

	confirm({
		title: trans("user-groups.confirm_delete_header"),
		message: trans("user-groups.confirm_delete_message"),
		acceptLabel: trans("user-groups.delete"),
		rejectLabel: trans("user-groups.cancel"),
		severity: "danger",
	}).then((confirmed) => {
		if (!confirmed) {
			return;
		}
		UserGroupService.deleteUserGroup(group.id).then(fetchUserGroups);
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
			detail: sprintf(trans("user-groups.remove_success"), member.username, group.name),
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
