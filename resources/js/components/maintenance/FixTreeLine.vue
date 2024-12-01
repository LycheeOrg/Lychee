<template>
	<div class="flex justify-between hover:bg-primary-emphasis/5 gap-8 items-center md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mx-auto">
		<div class="w-1/2" @mouseenter="setHoverId">
			<span v-if="props.album.prefix.length > 4" class="font-mono" v-html="props.album.prefix.slice(0, -2)" />
			<span
				:class="{
					'font-bold text-primary-emphasis': isHoverParent || isHoverId,
				}"
				v-if="props.album.prefix.length > 0"
			>
				└
			</span>
			<span
				:class="{
					'font-bold text-primary-emphasis': isHoverParent || isHoverId,
				}"
				>{{ props.album.title }}</span
			>
		</div>
		<div class="flex w-1/4 gap-4">
			<div class="flex">
				<InputNumber
					class="border-0 w-full px-2"
					pt:pcInputText:root:class="w-full border-0 px-3 py-1.5 w-full border-b hover:border-b-primary-400 focus:border-b-primary-400"
					v-model="lft"
					:invalid="lft === null || lft === undefined || lft === 0 || props.album.isDuplicate_lft"
					mode="decimal"
					:step="1"
					placeholder="_lft"
					@update:modelValue="console.log($event)"
				/>
				<Button text severity="secondary" icon="pi pi-angle-up" class="py-0.5" @click="incrementLft" />
				<Button text severity="secondary" icon="pi pi-angle-down" class="py-0.5" @click="decrementLft" />
			</div>
			<div class="flex">
				<InputNumber
					class="border-0 w-full px-2"
					pt:pcInputText:root:class="w-full border-0 px-3 py-1.5 w-full border-b hover:border-b-primary-400 focus:border-b-primary-400"
					v-model="rgt"
					:invalid="rgt === null || rgt === undefined || rgt === 0 || props.album.isDuplicate_rgt"
					mode="decimal"
					:step="1"
					placeholder="_rgt"
				/>
				<Button text severity="secondary" icon="pi pi-angle-up" class="py-0.5" @click="incrementRgt" />
				<Button text severity="secondary" icon="pi pi-angle-down" class="py-0.5" @click="decrementRgt" />
			</div>
		</div>
		<div class="flex w-1/4 justify-between items-center">
			<div
				@mouseenter="setHoverId"
				:class="{
					'font-bold text-primary-emphasis': isHoverId,
				}"
			>
				{{ props.album.trimmedId }}
				<LeftWarn v-if="props.album.isDuplicate_lft" class="ml-2" />
				<RightWarn v-if="props.album.isDuplicate_rgt" class="ml-2" />
			</div>
			<Inplace ref="inplace">
				<template #display>
					<span
						@mouseenter="setHoverParent"
						:class="{
							' text-muted-color-emphasis': props.album.trimmedParentId === 'root',
							'!text-danger-600 font-bold': !props.album.isExpectedParentId,
							'font-bold text-primary-emphasis': isHoverParent,
						}"
						>{{ (parentId ?? "root").slice(0, 6) }}</span
					>
				</template>
				<template #content>
					<Select
						class="border-none"
						v-model="parentId"
						filter
						resetFilterOnHide
						showClear
						:options="props.parentIdOptions"
						@update:modelValue="updateParentId"
						@hide="close"
					>
						<template #value="slotProps">
							<div v-if="slotProps.value">
								{{ slotProps.value.slice(0, 6) }}
							</div>
							<span v-else>
								{{ "root" }}
							</span>
						</template>
						<template #option="slotProps">
							<div>
								{{ slotProps.option.slice(0, 6) }}
							</div>
						</template>
					</Select>
				</template>
			</Inplace>
		</div>
	</div>
</template>
<script setup lang="ts">
import { ref, type Ref, watch } from "vue";
import { AugmentedAlbum } from "@/composables/album/treeOperations";
import InputNumber from "primevue/inputnumber";
import Button from "primevue/button";
import LeftWarn from "./mini/LeftWarn.vue";
import RightWarn from "./mini/RightWarn.vue";
import Inplace from "primevue/inplace";
import Select from "primevue/select";

const props = defineProps<{
	album: AugmentedAlbum;
	isHoverId: boolean;
	isHoverParent: boolean;
	parentIdOptions: string[];
}>();

const inplace = ref();

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

function updateParentId(val: string) {
	// guarantee we never have undefined.
	parentId.value = val ?? null;
	close();
}

function close() {
	inplace.value.close();
}

watch(
	() => [props.isHoverId, props.isHoverParent],
	([newIsHoverId, newIsHoverParent]) => {
		isHoverId.value = newIsHoverId;
		isHoverParent.value = newIsHoverParent;
	},
);
</script>
