<template>
	<span ref="anchorEl" class="hidden" />
	<Transition name="scroll-top-fade">
		<UButton
			v-if="visible"
			icon="prime:arrow-up"
			color="neutral"
			variant="solid"
			class="fixed bottom-5 right-5 rounded-full z-50 shadow-lg"
			@click="scrollToTop"
		/>
	</Transition>
</template>
<script setup lang="ts">
import { onMounted, onUnmounted, ref } from "vue";

const props = withDefaults(
	defineProps<{
		target?: "window" | "parent";
		threshold?: number;
	}>(),
	{
		target: "window",
		threshold: 400,
	},
);

const anchorEl = ref<HTMLElement | null>(null);
const visible = ref(false);
let scrollEl: HTMLElement | Window = window;

function getScrollTop(): number {
	return scrollEl instanceof Window ? window.scrollY : scrollEl.scrollTop;
}

function onScroll() {
	visible.value = getScrollTop() > props.threshold;
}

function scrollToTop() {
	if (scrollEl instanceof Window) {
		window.scrollTo({ top: 0, behavior: "smooth" });
	} else {
		scrollEl.scrollTo({ top: 0, behavior: "smooth" });
	}
}

onMounted(() => {
	scrollEl = props.target === "parent" ? (anchorEl.value?.parentElement ?? window) : window;
	scrollEl.addEventListener("scroll", onScroll);
	onScroll();
});

onUnmounted(() => {
	scrollEl.removeEventListener("scroll", onScroll);
});
</script>
<style scoped>
.scroll-top-fade-enter-active,
.scroll-top-fade-leave-active {
	transition: opacity 0.2s ease-in-out;
}
.scroll-top-fade-enter-from,
.scroll-top-fade-leave-to {
	opacity: 0;
}
</style>
