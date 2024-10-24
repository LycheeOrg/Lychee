<template>
	<CreateEditUser class="mt-10" @refresh="load" v-model:visible="isCreateUserVisible" :user="selectedUser" :is-edit="isEdit" />
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<router-link :to="{ name: 'gallery' }">
				<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text />
			</router-link>
		</template>

		<template #center>
			{{ $t("lychee.USERS") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel class="border-0 max-w-3xl mx-auto">
		<div class="w-full mb-10 text-muted-color-emphasis flex items-center gap-4">
			<div>
				<p>This page allows you to manage users.</p>
				<ul class="mt-1">
					<li class="ml-4 pt-2"><i class="pi pi-upload" /> : When selected, the user can upload content.</li>
					<li class="ml-4 pt-2"><i class="pi pi-lock-open" /> : When selected, the user can modify their profile (username, password).</li>
					<li class="ml-4 pt-2" v-if="is_se_enabled">
						<i class="pi pi-chart-pie" /> : When set, the user has a space quota for pictures (in kB).
					</li>
				</ul>
			</div>
			<Button @click="createUser" severity="primary" class="border-none p-3">Create a new user</Button>
		</div>
		<div class="flex flex-col">
			<div class="flex flex-wrap md:flex-nowrap gap-2 justify-center border-b border-solid border-b-surface-700 mb-4 pb-4">
				<div class="w-3/6 flex">
					<span class="w-2/3 font-bold">{{ $t("lychee.USERNAME") }}</span>
					<div class="w-1/3 flex justify-evenly">
						<span class="w-full text-center" v-tooltip.top="'When selected, the user can upload content.'"
							><i class="pi pi-upload"
						/></span>
						<span class="w-full text-center" v-tooltip.top="'When selected, the user can modify their profile (username, password).'">
							<i class="pi pi-lock-open" />
						</span>
						<span
							v-if="isQuotaEnabled"
							class="w-full text-center"
							v-tooltip.top="'When selected, the user is limited in the quatity of picture they can upload (in kB).'"
						>
							<i class="pi pi-chart-pie" />
						</span>
					</div>
				</div>
				<span class="w-1/6"></span>
				<span class="w-1/6"></span>
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
	</Panel>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import Button from "primevue/button";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import CreateEditUser from "@/components/forms/users/CreateEditUser.vue";
import UserManagementService from "@/services/user-management-service";
import ListUser from "@/components/forms/users/ListUser.vue";
import { useToast } from "primevue/usetoast";
import { storeToRefs } from "pinia";
import { useLycheeStateStore } from "@/stores/LycheeState";

const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const { is_se_preview_enabled, is_se_enabled } = storeToRefs(lycheeStore);

const users = ref([] as App.Http.Resources.Models.UserManagementResource[]);
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
		toast.add({ severity: "success", summary: "Success", detail: "User deleted" });
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
