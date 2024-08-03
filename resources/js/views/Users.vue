<template>
	<Toolbar class="w-full border-0">
		<template #start>
			<router-link :to="{ name: 'gallery' }">
				<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="" />
			</router-link>
		</template>

		<template #center>
			{{ $t("lychee.USERS") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel class="border-0">
		<DataTable :value="users" tableStyle="min-width: 50rem">
			<Column field="username" header="username"></Column>
			<Column field="may_upload" header="upload"></Column>
			<Column field="may_edit_own_settings" header="edit"></Column>
		</DataTable>
	</Panel>
	<Panel class="border-0"> </Panel>
</template>
<script setup lang="ts">
import UsersService from "@/services/users-service";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import { ref } from "vue";

const users = ref([] as App.Http.Resources.Models.UserManagementResource[]);

function load() {
	UsersService.get().then((response) => {
		console.log(response.data.users);
		users.value = response.data.users;
	});
}

load();
</script>
