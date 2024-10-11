<template>
	<CreateUser class="mt-10" @createUser="load" v-model:visible="isCreateUserVisible" />
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
		<div class="w-full mb-10 text-muted-color flex items-center gap-4">
			<div>
				<p>This pages allows you to manage users.</p>
				<ul class="mt-1">
					<li class="ml-4 pt-2"><i class="pi pi-upload" /> : When selected, the user can upload content.</li>
					<li class="ml-4 pt-2"><i class="pi pi-lock-open" /> : When selected, the user can modify their profile (username, password).</li>
				</ul>
			</div>
			<Button @click="isCreateUserVisible = true" severity="primary" class="border-none p-3">Create a new user</Button>
		</div>
		<div class="flex flex-col">
			<div class="flex flex-wrap md:flex-nowrap gap-2 justify-center">
				<span class="w-1/3 font-bold">{{ $t("lychee.USERNAME") }}</span>
				<span class="w-1/3 font-bold">{{ $t("lychee.PASSWORD") }}</span>
				<span class="w-1/12 text-center" v-tooltip.top="'When selected, the user can upload content.'"><i class="pi pi-upload" /></span>
				<span class="w-1/12 text-center" v-tooltip.top="'When selected, the user can modify their profile (username, password).'"
					><i class="pi pi-lock-open"
				/></span>
				<span class="w-1/6"></span>
			</div>

			<EditUser v-for="user in users" :key="user.id" :user="user" @deleteUser="deleteUser" />
		</div>
	</Panel>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Button from "primevue/button";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import CreateUser from "@/components/forms/users/CreateUser.vue";
import EditUser from "@/components/forms/users/EditUser.vue";
import UserManagementService from "@/services/user-management-service";

const users = ref([] as App.Http.Resources.Models.UserManagementResource[]);

const isCreateUserVisible = ref(false);

function load() {
	UserManagementService.get().then((response) => {
		users.value = response.data;
	});
}

function deleteUser(id: number) {
	UserManagementService.delete({ id: id }).then(() => {
		load();
	});
}

load();
</script>
