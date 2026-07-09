<template>
	<InviteUser v-model:visible="isInviteUserVisible" />
	<CreateEditUser v-model:visible="isCreateUserVisible" class="mt-10" :user="selectedUser" :is-edit="isEdit" @refresh="load" />
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("users.title") }}
	</UHeader>
	<div class="max-w-6xl mx-auto p-4">
		<div class="flex flex-wrap justify-center gap-4">
			<div class="w-full lg:w-2/3 xl:w-3/6">
				<p class="text-highlighted">{{ $t("users.description") }}</p>
				<div class="flex justify-between mt-8 mb-8">
					<UButton color="primary" @click="inviteUser">{{ $t("users.invite.button") }}</UButton>
					<UButton color="primary" @click="createUser">{{ $t("users.create") }}</UButton>
				</div>
				<div class="flex flex-col">
					<div class="flex flex-wrap md:flex-nowrap border-b border-solid border-b-neutral-700 mb-4 pb-4">
						<div class="w-9/12 lg:w-8/12 flex">
							<span class="w-2/3 font-bold">{{ $t("users.username") }}</span>
							<div class="w-1/3 flex justify-evenly">
								<UTooltip :text="$t('users.upload_rights')"
									><span class="w-full text-center"><UIcon name="prime:upload" /></span
								></UTooltip>
								<UTooltip :text="$t('users.edit_rights')">
									<span class="w-full text-center"><UIcon name="prime:lock-open" /></span>
								</UTooltip>
								<UTooltip :text="$t('users.upload_trust_level')">
									<span class="w-full text-center"><UIcon name="prime:shield" /></span>
								</UTooltip>
								<UTooltip v-if="isQuotaEnabled" :text="$t('users.quota')">
									<span class="w-full text-center"><UIcon name="prime:chart-pie" /></span>
								</UTooltip>
							</div>
						</div>
						<span class="w-1/12 lg:w-2/12"></span>
						<span class="w-1/12 lg:w-2/12"></span>
					</div>

					<ListUser
						v-for="user in users"
						:key="user.id"
						:user="user"
						:total-used-space="totalUsedSpace"
						:is-quota-enabled="isQuotaEnabled"
						@delete-user="deleteUser"
						@edit-user="editUser"
					/>
				</div>
			</div>
			<UCard class="text-muted w-full lg:w-2/3 xl:w-2/6 xl:pl-12">
				<template #header>{{ $t("users.legend") }}</template>
				<ul class="text-sm">
					<li class="ltr:ml-2 rtl:mr-2 pt-2 flex items-start gap-x-4">
						<UIcon name="prime:upload" />
						<span>{{ $t("users.upload_rights") }}</span>
					</li>
					<li class="ltr:ml-2 rtl:mr-2 pt-2 flex items-start gap-x-4">
						<UIcon name="prime:lock-open" />
						<span>{{ $t("users.edit_rights") }}</span>
					</li>
					<li class="ltr:ml-2 rtl:mr-2 pt-2 flex items-start gap-x-4">
						<UIcon name="prime:shield" class="text-success" />
						<span>{{ $t("users.upload_trust_level") }}</span>
					</li>
					<li v-if="is_se_enabled" class="ltr:ml-2 rtl:mr-2 pt-2 flex items-start gap-x-4">
						<UIcon name="prime:chart-pie" />
						<span>{{ $t("users.quota") }}</span>
					</li>
				</ul>
			</UCard>
		</div>
	</div>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import { storeToRefs } from "pinia";
import CreateEditUser from "@/v8/components/forms/users/CreateEditUser.vue";
import ListUser from "@/v8/components/forms/users/ListUser.vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import UserManagementService from "@/services/user-management-service";
import UsersService from "@/services/users-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { trans } from "laravel-vue-i18n";
import InviteUser from "@/v8/components/modals/InviteUser.vue";
import { useAppToast } from "@/v8/composables/useAppToast";

const lycheeStore = useLycheeStateStore();
lycheeStore.load();
const { is_se_enabled } = storeToRefs(lycheeStore);

const users = ref<App.Http.Resources.Models.UserManagementResource[]>([]);
const isCreateUserVisible = ref(false);
const isInviteUserVisible = ref(false);
const totalUsedSpace = ref(0);

const toast = useAppToast();

const selectedUser = ref<App.Http.Resources.Models.UserManagementResource | undefined>(undefined);
const isEdit = ref(false);
const isQuotaEnabled = computed(() => is_se_enabled && users.value.reduce((acc, user) => acc || user.quota_kb !== null, false));

function load() {
	UserManagementService.get().then((response) => {
		users.value = response.data;
		totalUsedSpace.value = response.data.reduce((acc, user) => acc + (user.space ?? 0), 0);
	});
}

function inviteUser() {
	isInviteUserVisible.value = true;
}

function deleteUser(id: number) {
	UserManagementService.delete({ id: id }).then(() => {
		toast.add({ severity: "success", summary: "Success", detail: trans("users.user_deleted") });

		// Clear user count as it is cachable.
		UsersService.clearCount();

		load();
	});
}

function editUser(id: number) {
	selectedUser.value = users.value.find((user) => user.id === id);
	isEdit.value = true;
	isCreateUserVisible.value = true;
}

function createUser() {
	selectedUser.value = undefined;
	isEdit.value = false;
	isCreateUserVisible.value = true;
}

load();
</script>
