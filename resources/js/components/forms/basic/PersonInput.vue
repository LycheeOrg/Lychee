<template>
	<div class="flex flex-col gap-4">
		<!-- Select existing person -->
		<div>
			<label class="block text-sm text-muted-color mb-1">{{ $t("people.assignment.select_person") }}</label>
			<Select
				v-model="personId"
				:options="people"
				option-label="name"
				option-value="id"
				:placeholder="$t('people.assignment.select_person')"
				class="w-full"
				filter
				:loading="loading"
				@change="newPersonName = ''"
			>
				<template #option="slotProps">
					<div class="flex items-center gap-2">
						<img
							v-if="slotProps.option.representative_crop_url"
							:src="slotProps.option.representative_crop_url"
							class="w-6 h-6 rounded-full object-cover shrink-0"
							alt=""
						/>
						<i v-else class="pi pi-user w-6 h-6 flex items-center justify-center text-muted-color shrink-0" />
						<span class="flex-1 truncate">{{ slotProps.option.name }}</span>
						<span class="text-xs text-muted-color shrink-0">{{ slotProps.option.face_count }}</span>
					</div>
				</template>
			</Select>
		</div>

		<!-- Or create new person -->
		<div>
			<label class="block text-sm text-muted-color mb-1">{{ $t("people.assignment.new_person") }}</label>
			<InputText
				v-model="newPersonName"
				class="w-full"
				:placeholder="$t('people.assignment.new_person_placeholder')"
				@input="personId = undefined"
			/>
		</div>
	</div>
</template>

<script setup lang="ts">
import Select from "primevue/select";
import InputText from "primevue/inputtext";
import { usePeopleList } from "@/composables/usePeopleList";

const personId = defineModel<string | undefined>("personId", { default: undefined });
const newPersonName = defineModel<string>("newPersonName", { default: "" });

const { people, loading, load } = usePeopleList();

/**
 * Selects an existing person by exact name match (used for suggestion shortcuts).
 * Returns true if a match was found and selected.
 */
function selectByName(name: string): boolean {
	const match = people.value.find((p) => p.name === name);
	if (!match) {
		return false;
	}
	personId.value = match.id;
	newPersonName.value = "";
	return true;
}

/** Resets the selection and reloads the person list; call when the host dialog opens. */
function reset(): void {
	personId.value = undefined;
	newPersonName.value = "";
	load();
}

defineExpose({ selectByName, reset });
</script>
