<template>
	<section
		id="featured"
		ref="sectionEl"
		class="px-6 py-24 transition-all duration-700"
		:class="isVisible ? props.sectionRevealClass : sectionHiddenClass"
	>
		<h2 class="text-2xl font-bold uppercase mb-8 text-center">{{ $t("landing.portfolio.featured") }}</h2>
		<div ref="gridEl" class="relative max-w-7xl mx-auto">
			<a
				v-for="item in props.items"
				:key="`${item.item_type}-${item.id}`"
				:href="item.url"
				class="group absolute overflow-hidden"
				:data-width="item.width ?? 1"
				:data-height="item.height ?? 1"
			>
				<img
					:src="item.thumb_url"
					:srcset="item.thumb_url_2x ? `${item.thumb_url} 1x, ${item.thumb_url_2x} 2x` : undefined"
					:alt="item.title"
					loading="lazy"
					class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
				/>
				<div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3">
					<span class="text-xs uppercase">{{ item.title }}</span>
				</div>
			</a>
		</div>
	</section>
</template>
<script setup lang="ts">
import { computed, onMounted, onUnmounted, onUpdated, ref } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { ChildNodeWithDataStyle } from "@/layouts/types";
import { getRatio } from "@/layouts/ratio";
import { initLayouts, masonry } from "@/v8/layouts/wasmLayouts";
import { applyLayoutResult } from "@/v8/layouts/applyLayoutResult";
import { useLtRorRtL } from "@/utils/Helpers";
import { useScrollReveal } from "@/v8/composables/useScrollReveal";

const props = defineProps<{
	items: App.Http.Resources.GalleryConfigs.LandingFeaturedContentResource[];
	// Passed through from the parent's `effectivePreset` - identical logic to the About section's
	// own isScrollDriven/sectionRevealClass, computed once there rather than duplicated here.
	isScrollDriven: boolean;
	sectionRevealClass: string;
}>();

const sectionHiddenClass = computed(() => (props.isScrollDriven ? "opacity-0" : ""));
const { el: sectionEl, isVisible } = useScrollReveal(computed(() => props.isScrollDriven));

const { isLTR } = useLtRorRtL();
const gridEl = ref<HTMLElement | null>(null);

// Real photo/album-cover proportions (LandingFeaturedContentResource's width/height, sourced from
// the "small" size variant - see that resource's applyThumb()) let the WASM masonry engine pack
// tiles by their natural aspect ratio, matching how the main gallery grid lays out thumbnails.
async function runMasonryLayout() {
	const el = gridEl.value;
	if (!el) return;
	const containerWidth = el.clientWidth;
	if (containerWidth <= 0) return;
	const gridItems: ChildNodeWithDataStyle[] = [...el.childNodes].filter((node) => node.nodeType === 1);
	if (gridItems.length === 0) {
		el.style.height = "0px";
		return;
	}
	const ratios = Float64Array.from(getRatio(gridItems));
	await initLayouts();
	const result = masonry(ratios, containerWidth, 220, 16);
	applyLayoutResult(el, gridItems, result, isLTR() ? "left" : "right");
}

const debouncedLayout = useDebounceFn(runMasonryLayout, 100);
let resizeObserver: ResizeObserver | null = null;

onMounted(() => {
	runMasonryLayout();
	resizeObserver = new ResizeObserver(debouncedLayout);
	if (gridEl.value) resizeObserver.observe(gridEl.value);
});

onUpdated(() => {
	runMasonryLayout();
});

onUnmounted(() => {
	resizeObserver?.disconnect();
});
</script>
