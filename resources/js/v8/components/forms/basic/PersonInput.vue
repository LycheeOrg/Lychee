<template>
	<div class="flex flex-col gap-4">
		<!-- Select existing person -->
		<div>
			<label class="block text-sm text-muted mb-1">{{ $t("people.assignment.select_person") }}</label>
			<USelectMenu
				v-model="personId"
				:items="people"
				value-key="id"
				label-key="name"
				:placeholder="$t('people.assignment.select_person')"
				class="w-full"
				:loading="loading"
				@update:model-value="newPersonName = ''"
			>
				<template #item="{ item }">
					<div class="flex items-center gap-2">
						<img
							v-if="item.representative_crop_url"
							:src="item.representative_crop_url"
							class="w-6 h-6 rounded-full object-cover shrink-0"
							alt=""
						/>
						<UIcon v-else name="lucide:user" class="w-6 h-6 flex items-center justify-center text-muted shrink-0" />
						<span class="flex-1 truncate">{{ item.name }}</span>
						<span class="text-xs text-muted shrink-0">{{ item.face_count }}</span>
					</div>
				</template>
			</USelectMenu>
		</div>

		<!-- Or create new person -->
		<div>
			<label class="block text-sm text-muted mb-1">{{ $t("people.assignment.new_person") }}</label>
			<UInput
				v-model="newPersonName"
				class="w-full"
				:placeholder="$t('people.assignment.new_person_placeholder')"
				@input="personId = undefined"
			/>
		</div>
	</div>
</template>

<script setup lang="ts">
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
