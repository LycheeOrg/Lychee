import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { computed } from "vue";

/**
 * Ken-Burns-on-hover wiring shared by every photo grid wrapper -
 * `PhotoThumbPanelList.vue` on the v2 path and `PhotoGridVirtual.vue` on the
 * Struct-of-Arrays path. The wrapper toggles the `photo-ken-burns-on-hover`
 * class and sets the two CSS custom properties the rule in `app-v8.css` reads,
 * so `.photo:hover .thumb-image` zooms identically on both paths.
 */
export function useKenBurnsHover() {
	const { is_photo_ken_burns_on_hover, photo_ken_burns_on_hover_scale, photo_ken_burns_on_hover_duration } = storeToRefs(useLycheeStateStore());

	const kenBurnsClass = computed(() => ({ "photo-ken-burns-on-hover": is_photo_ken_burns_on_hover.value }));

	const kenBurnsStyle = computed(() => ({
		"--photo-ken-burns-scale": `${photo_ken_burns_on_hover_scale.value / 100 + 1}`,
		"--photo-ken-burns-duration": `${photo_ken_burns_on_hover_duration.value}s`,
	}));

	return { kenBurnsClass, kenBurnsStyle };
}
