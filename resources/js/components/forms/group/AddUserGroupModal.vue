<template>
	<Dialog v-model:visible="visible" header="Add New User Group" pt:root:class="border-none" modal :dismissable-mask="true" :closable="true">
		<template #container="{ closeCallback }">
			<form @submit.prevent="submitForm">
				<div class="flex flex-col gap-4 bg-gradient-to-b from-bg-300 to-bg-400 relative w-full md:w-lg rounded-md text-muted-color p-9">
					<Message v-if="error" severity="danger">{{ error }}</Message>
					<FloatLabel variant="on">
						<InputText id="name" v-model="name" required />
						<label for="name">{{ $t("user-groups.create.name") }}</label>
					</FloatLabel>
					<FloatLabel variant="on">
						<Textarea id="description" v-model="description" :rows="3" />
						<label for="description">{{ $t("user-groups.create.description") }}</label>
					</FloatLabel>
					<FloatLabel v-if="groupId === undefined" variant="on">
						<AutoComplete
							id="users"
							v-model="users"
							force-selection
							option-label="username"
							multiple
							class="border-b hover:border-b-0 w-full"
							pt:inputmultiple:class="w-full border-t-0 border-l-0 border-r-0 border-b hover:border-b-primary-400 focus:border-b-primary-400"
							:suggestions="filteredUsers"
							dropdown
							@complete="filterUser"
						/>
						<label for="users">{{ $t("user-groups.create.users") }}</label>
					</FloatLabel>
				</div>
				<div class="flex w-full justify-content-end mt-3">
					<Button
						:label="$t('user-groups.create.cancel')"
						icon="pi pi-times"
						class="w-full border-none rounded-none rounded-bl-xl"
						severity="secondary"
						@click="closeCallback"
					/>
					<Button
						v-if="groupId === undefined"
						:label="$t('user-groups.create.create')"
						icon="pi pi-check"
						type="submit"
						class="w-full rounded-none rounded-br-xl border-none"
					/>
					<Button
						v-else
						:label="$t('user-groups.create.edit')"
						icon="pi pi-check"
						type="submit"
						class="w-full rounded-none rounded-br-xl border-none"
					/>
				</div>
			</form>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { UserGroupService } from "@/services/user-group-service";
import { Ref } from "vue";
import Dialog from "primevue/dialog";
import FloatLabel from "primevue/floatlabel";
import InputText from "@/components/forms/basic/InputText.vue";
import Textarea from "@/components/forms/basic/Textarea.vue";
import Button from "primevue/button";
import Message from "primevue/message";
import AutoComplete from "primevue/autocomplete";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	userList: App.Http.Resources.Models.LightUserResource[];
}>();

const visible = defineModel("visible") as Ref<boolean>;
const groupId = defineModel("groupId") as Ref<number | undefined>;
const name = defineModel("name") as Ref<string | undefined>;
const description = defineModel("description") as Ref<string | undefined>;

const error = ref<undefined | string>(undefined);
const filteredUsers = ref<App.Http.Resources.Models.LightUserResource[]>([]);

const users = ref<App.Http.Resources.Models.LightUserResource[]>([]);

const emit = defineEmits(["refresh"]);

function filterUser(event: { query: string }) {
	if (props.userList.length === 0) return;

	const query = event.query.toLowerCase();
	filteredUsers.value = props.userList.filter((user) => user.username.toLowerCase().includes(query));
}

function submitForm() {
	if (groupId.value === undefined) {
		return createGroup();
	}
	return updateGroup();
}

function createGroup() {
	if (name.value === undefined || name.value.trim() === "") {
		error.value = trans("user-groups.create.name_required");
		return;
	}

	UserGroupService.createUserGroup(name.value, description.value ?? "")
		.then((data) => {
			Promise.all(
				users.value.map((user) => {
					return UserGroupService.addUserToGroup(data.data.id, user.id, "member");
				}),
			).then(() => {
				closeModal();
				emit("refresh");
				name.value = "";
				description.value = "";
				users.value = [];
				error.value = undefined;
			});
		})
		.catch((error) => {
			error.value = error.response?.data?.message || "An error occurred while creating the user group.";
		});
}

function updateGroup() {
	if (groupId.value === undefined) {
		return;
	}

	if (name.value === undefined || name.value.trim() === "") {
		error.value = trans("user-groups.create.name_required");
		return;
	}

	UserGroupService.updateUserGroup(groupId.value, name.value, description.value ?? "")
		.then(() => {
			closeModal();
			emit("refresh");
			name.value = "";
			description.value = "";
			users.value = [];
			error.value = undefined;
		})
		.catch((error) => {
			error.value = error.response?.data?.message || "An error occurred while updating the user group.";
		});
}

function closeModal() {
	visible.value = false;
}
</script>
