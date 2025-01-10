<template>
	<CreateEditUser class="mt-10" @refresh="load" v-model:visible="isCreateUserVisible" :user="selectedUser" :is-edit="isEdit" />
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ $t("users.title") }}
		</template>

		<template #end></template>
	</Toolbar>
	<Panel class="border-0 max-w-6xl mx-auto">
		<div class="flex flex-wrap justify-center">
			<div class="w-full lg:w-2/3 xl:w-3/6">
				<p class="text-muted-color-emphasis">{{ $t("users.description") }}</p>
				<div class="flex justify-end mt-8 mb-8">
					<Button @click="createUser" severity="primary" class="border-none p-3">{{ $t("users.create") }}</Button>
				</div>
				<div class="flex flex-col">
					<div class="flex flex-wrap md:flex-nowrap border-b border-solid border-b-surface-700 mb-4 pb-4">
						<div class="w-9/12 lg:w-8/12 flex">
							<span class="w-2/3 font-bold">{{ $t("users.username") }}</span>
							<div class="w-1/3 flex justify-evenly">
								<span class="w-full text-center" v-tooltip.top="$t('users.upload_rights')"><i class="pi pi-upload" /></span>
								<span class="w-full text-center" v-tooltip.top="$t('users.edit_rights')">
									<i class="pi pi-lock-open" />
								</span>
								<span v-if="isQuotaEnabled" class="w-full text-center" v-tooltip.top="$t('users.quota')">
									<i class="pi pi-chart-pie" />
								</span>
							</div>
						</div>
						<span class="w-1/12 lg:w-2/12"></span>
						<span class="w-1/12 lg:w-2/12"></span>
					</div>

					<ListUser
						v-for="user in users"
						:key="user.id"
						:user="user"
						@delete-user="deleteUser"
						@edit-user="editUser"
						:total-used-space="totalUsedSpace"
						:is-quota-enabled="isQuotaEnabled"
					/>
				</div>
			</div>
			<Card class="text-muted-color w-full lg:w-2/3 xl:w-2/6 xl:pl-12" :pt:body:class="'px-0 lg:pt-0'">
				<template #title>{{ $t("users.legend") }}</template>
				<template #content>
					<ul class="text-sm">
						<li class="ml-2 pt-2 flex items-start gap-x-4">
							<i class="pi pi-upload"></i>
							<span>When selected, the user can upload content.</span>
						</li>
						<li class="ml-2 pt-2 flex items-start gap-x-4">
							<i class="pi pi-lock-open"></i>
							<span>When selected, the user can modify their profile (username, password).</span>
						</li>
						<li class="ml-2 pt-2 flex items-start gap-x-4" v-if="is_se_enabled">
							<i class="pi pi-chart-pie"></i>
							<span>{{ $t("users.quota") }}</span>
						</li>
					</ul>
				</template>
			</Card>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import { storeToRefs } from "pinia";
import Button from "primevue/button";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import Card from "primevue/card";
import { useToast } from "primevue/usetoast";
import CreateEditUser from "@/components/forms/users/CreateEditUser.vue";
import ListUser from "@/components/forms/users/ListUser.vue";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import UserManagementService from "@/services/user-management-service";
import UsersService from "@/services/users-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { trans } from "laravel-vue-i18n";

const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const { is_se_preview_enabled, is_se_enabled } = storeToRefs(lycheeStore);

const users = ref<App.Http.Resources.Models.UserManagementResource[]>([]);
const isCreateUserVisible = ref(false);
const totalUsedSpace = ref(0);

const toast = useToast();

const selectedUser = ref<App.Http.Resources.Models.UserManagementResource | undefined>(undefined);
const isEdit = ref(false);
const isQuotaEnabled = computed(() => is_se_enabled && users.value.reduce((acc, user) => acc || user.quota_kb !== null, false));

function load() {
	UserManagementService.get().then((response) => {
		users.value = response.data;
		totalUsedSpace.value = response.data.reduce((acc, user) => acc + (user.space ?? 0), 0);
	});
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
