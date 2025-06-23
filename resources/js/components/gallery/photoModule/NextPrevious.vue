<template>
	<div class="absolute w-1/6 h-1/2 top-1/2 -translate-y-1/2 group" :class="props.is_next ? 'ltr:right-0 rtl:left-0' : 'ltr:left-0 rtl:right-0'">
		<router-link
			:to="photoRoute(props.photoId)"
			:id="props.is_next ? 'nextButton' : 'previousButton'"
			:class="{
				'absolute top-1/2 border border-solid border-neutral-200 dark:border-neutral-700': true,
				'-mt-5 transition-all opacity-0 group-hover:opacity-100 bg-cover': true,
				'py-10.75 px-11': photo_previous_next_size === 'large',
				'py-2 px-3': photo_previous_next_size === 'small',
				'hover:border-primary-400 fill-neutral-400 hover:fill-primary-400': true,
				'-right-px group-hover:translate-x-0 translate-x-full': (props.is_next && isLTR()) || (!props.is_next && !isLTR()),
				'-left-px group-hover:translate-x-0 -translate-x-full': (!props.is_next && isLTR()) || (props.is_next && !isLTR()),
			}"
			:style="props.style"
		>
			<MiniIcon :icon="props.is_next ? 'caret-right' : 'caret-left'" :fill="''" class="m-0 h-6 w-5" v-if="isLTR()" />
			<MiniIcon :icon="props.is_next ? 'caret-left' : 'caret-right'" :fill="''" class="m-0 h-6 w-5" v-else />
		</router-link>
	</div>
</template>
<script setup lang="ts">
import MiniIcon from "@/components/icons/MiniIcon.vue";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { useRouter } from "vue-router";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { useLtRorRtL } from "@/utils/Helpers";

const { isLTR } = useLtRorRtL();
const lycheeStateStore = useLycheeStateStore();
const { photo_previous_next_size } = storeToRefs(lycheeStateStore);

const props = defineProps<{
	is_next: boolean;
	albumId: string;
	photoId: string;
	style: string;
}>();

const router = useRouter();
const { photoRoute } = usePhotoRoute(router);
</script>
