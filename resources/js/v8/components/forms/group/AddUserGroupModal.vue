<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #header>
			<span class="font-bold">Add New User Group</span>
		</template>
		<template #body>
			<form @submit.prevent="submitForm">
				<div class="flex flex-col gap-4">
					<UAlert v-if="error" color="error" variant="soft" :description="error" />
					<UFormField :label="$t('user-groups.create.name')">
						<UInput id="name" v-model="name" class="w-full" required />
					</UFormField>
					<UFormField :label="$t('user-groups.create.description')">
						<UTextarea id="description" v-model="descriptionForInput" :rows="3" class="w-full" />
					</UFormField>
					<UFormField v-if="groupId === undefined" :label="$t('user-groups.create.users')">
						<USelectMenu id="users" v-model="users" :items="props.userList" multiple label-key="username" class="w-full" />
					</UFormField>
				</div>
			</form>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton
					:label="$t('user-groups.create.cancel')"
					icon="prime:times"
					color="neutral"
					variant="soft"
					class="flex-1 justify-center"
					@click="visible = false"
				/>
				<UButton
					:label="groupId === undefined ? $t('user-groups.create.create') : $t('user-groups.create.edit')"
					icon="prime:check"
					class="flex-1 justify-center"
					@click="submitForm"
				/>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { UserGroupService } from "@/services/user-group-service";
import { Ref } from "vue";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	userList: App.Http.Resources.Models.LightUserResource[];
}>();

const visible = defineModel("visible") as Ref<boolean>;
const groupId = defineModel("groupId") as Ref<number | undefined>;
const name = defineModel("name") as Ref<string | undefined>;
const description = defineModel("description") as Ref<string | undefined>;

// UTextarea's v-model requires `string | undefined` (no null/undefined confusion with `""`).
const descriptionForInput = computed<string | undefined>({
	get: () => description.value ?? undefined,
	set: (v) => {
		description.value = v;
	},
});

const error = ref<undefined | string>(undefined);

const users = ref<App.Http.Resources.Models.LightUserResource[]>([]);

const emit = defineEmits(["refresh"]);

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
		.catch((err) => {
			error.value = err.response?.data?.message || "An error occurred while creating the user group.";
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
		.catch((err) => {
			error.value = err.response?.data?.message || "An error occurred while updating the user group.";
		});
}

function closeModal() {
	visible.value = false;
}
</script>
