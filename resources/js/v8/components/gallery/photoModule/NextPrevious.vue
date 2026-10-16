<template>
	<div
		ref="zone"
		class="absolute w-1/6 h-1/2 top-1/2 -translate-y-1/2 pointer-events-none"
		:class="props.is_next ? 'ltr:right-0 rtl:left-0' : 'ltr:left-0 rtl:right-0'"
	>
		<router-link
			:id="props.is_next ? 'nextButton' : 'previousButton'"
			:to="photoRoute(props.photoId)"
			:class="{
				'absolute top-1/2 border border-solid border-muted pointer-events-auto': true,
				'-mt-5 transition-all bg-cover': true,
				'opacity-0': !isHovered,
				'py-10.75 px-11': photo_previous_next_size === 'large',
				'py-2 px-3': photo_previous_next_size === 'small',
				'hover:border-primary-400 fill-neutral-400 hover:fill-primary-400': true,
				'-right-px': (props.is_next && isLTR()) || (!props.is_next && !isLTR()),
				'-left-px': (!props.is_next && isLTR()) || (props.is_next && !isLTR()),
				'translate-x-full': !isHovered && ((props.is_next && isLTR()) || (!props.is_next && !isLTR())),
				'-translate-x-full': !isHovered && ((!props.is_next && isLTR()) || (props.is_next && !isLTR())),
			}"
			:style="props.style"
		>
			<MiniIcon v-if="isLTR()" :icon="props.is_next ? 'caret-right' : 'caret-left'" :fill="''" class="m-0 h-6 w-5" />
			<MiniIcon v-else :icon="props.is_next ? 'caret-left' : 'caret-right'" :fill="''" class="m-0 h-6 w-5" />
		</router-link>
	</div>
</template>
<script setup lang="ts">
import MiniIcon from "@/v8/components/icons/MiniIcon.vue";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { useRouter } from "vue-router";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { useLtRorRtL } from "@/utils/Helpers";
import { ref } from "vue";
import { usePointerZone } from "@/v8/composables/photo/usePointerZone";

const { isLTR } = useLtRorRtL();
const lycheeStateStore = useLycheeStateStore();
const { photo_previous_next_size } = storeToRefs(lycheeStateStore);

const props = defineProps<{
	is_next: boolean;
	photoId: string;
	style: string;
}>();

const router = useRouter();
const { photoRoute } = usePhotoRoute(router);

// The reveal zone ignores pointer events so clicks reach the face overlay below; only the arrow is interactive.
const zone = ref<HTMLElement | null>(null);
const { isInside: isHovered } = usePointerZone(zone);
</script>
