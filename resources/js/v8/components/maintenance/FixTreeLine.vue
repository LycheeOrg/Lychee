<template>
	<div class="flex justify-between hover:bg-primary/5 gap-8 items-center md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mx-auto">
		<div class="w-1/2" @mouseenter="setHoverId">
			<span v-if="props.album.prefix.length > 4" class="font-mono" v-html="props.album.prefix.slice(0, -2)" />
			<span
				v-if="props.album.prefix.length > 0"
				:class="{
					'ltr:mr-2 rtl:ml-2': true,
					'font-bold text-primary': isHoverParent || isHoverId,
				}"
			>
				{{ isLTR() ? "└ " : "┘" }}
			</span>
			<span
				:class="{
					'font-bold text-primary': isHoverParent || isHoverId,
				}"
			>
				{{ props.album.title }}
			</span>
		</div>
		<div class="flex w-1/4 gap-4">
			<div class="flex">
				<UInputNumber
					class="w-full px-2"
					v-model="lft"
					:color="lft === null || lft === undefined || lft === 0 || props.album.isDuplicate_lft ? 'error' : undefined"
					:step="1"
					placeholder="_lft"
				/>
				<UButton variant="ghost" color="neutral" icon="lucide:chevron-up" class="py-0.5" @click="incrementLft" />
				<UButton variant="ghost" color="neutral" icon="lucide:chevron-down" class="py-0.5" @click="decrementLft" />
			</div>
			<div class="flex">
				<UInputNumber
					class="w-full px-2"
					v-model="rgt"
					:color="rgt === null || rgt === undefined || rgt === 0 || props.album.isDuplicate_rgt ? 'error' : undefined"
					:step="1"
					placeholder="_rgt"
				/>
				<UButton variant="ghost" color="neutral" icon="lucide:chevron-up" class="py-0.5" @click="incrementRgt" />
				<UButton variant="ghost" color="neutral" icon="lucide:chevron-down" class="py-0.5" @click="decrementRgt" />
			</div>
		</div>
		<div class="flex w-1/4 justify-between items-center">
			<div
				:class="{
					'font-bold text-primary': isHoverId,
				}"
				@mouseenter="setHoverId"
			>
				{{ props.album.trimmedId }}
				<LeftWarn v-if="props.album.isDuplicate_lft" class="ltr:ml-2 rtl:mr-2" />
				<RightWarn v-if="props.album.isDuplicate_rgt" class="ltr:ml-2 rtl:mr-2" />
			</div>
			<div v-if="!editingParent" class="cursor-pointer" @click="editingParent = true">
				<span
					:class="{
						'text-highlighted': props.album.trimmedParentId === 'root',
						'text-error! font-bold': !props.album.isExpectedParentId,
						'font-bold text-primary': isHoverParent,
					}"
					@mouseenter="setHoverParent"
					>{{ (parentId ?? "root").slice(0, 6) }}</span
				>
			</div>
			<USelectMenu
				v-else
				:model-value="parentId ?? undefined"
				class="w-32"
				searchable
				:items="props.parentIdOptions"
				@update:model-value="(v: string | undefined) => updateParentId(v)"
				@update:open="(o: boolean) => !o && close()"
			>
				<template #item-label="{ item }">{{ (item as string).slice(0, 6) }}</template>
				<template #default="{ modelValue }">
					<span v-if="modelValue">{{ (modelValue as string).slice(0, 6) }}</span>
					<span v-else>root</span>
				</template>
			</USelectMenu>
		</div>
	</div>
</template>
<script setup lang="ts">
import { ref, type Ref, watch } from "vue";
import { AugmentedAlbum } from "@/composables/album/treeOperations";
import LeftWarn from "./mini/LeftWarn.vue";
import RightWarn from "./mini/RightWarn.vue";
import { useLtRorRtL } from "@/utils/Helpers";

const { isLTR } = useLtRorRtL();

const props = defineProps<{
	album: AugmentedAlbum;
	isHoverId: boolean;
	isHoverParent: boolean;
	parentIdOptions: string[];
}>();

const editingParent = ref(false);

const lft = defineModel("lft") as Ref<number>;
const rgt = defineModel("rgt") as Ref<number>;
const parentId = defineModel("parentId") as Ref<string | null | undefined>;

const isHoverId = ref<boolean>(props.isHoverId);
const isHoverParent = ref<boolean>(props.isHoverParent);

const emits = defineEmits<{
	hoverId: [id: string];
	incrementLft: [];
	decrementLft: [];
	incrementRgt: [];
	decrementRgt: [];
}>();

function setHoverId() {
	emits("hoverId", props.album.trimmedId);
}

function setHoverParent() {
	emits("hoverId", props.album.trimmedParentId);
}

function incrementLft() {
	emits("incrementLft");
}

function decrementLft() {
	emits("decrementLft");
}

function incrementRgt() {
	emits("incrementRgt");
}

function decrementRgt() {
	emits("decrementRgt");
}

function updateParentId(val: string | undefined) {
	// guarantee we never have undefined.
	parentId.value = val ?? null;
	close();
}

function close() {
	editingParent.value = false;
}

watch(
	() => [props.isHoverId, props.isHoverParent],
	([newIsHoverId, newIsHoverParent]) => {
		isHoverId.value = newIsHoverId;
		isHoverParent.value = newIsHoverParent;
	},
);
</script>
