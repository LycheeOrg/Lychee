<template>
	<div id="landing" class="fixed inset-0 bg-black overflow-hidden">
		<LandingBackgroundImages
			:landscape="data.landing_background_landscape"
			:portrait="data.landing_background_portrait"
			:preview-orientation="previewOrientation"
			:wrapper-class="[entranceClass, restDelayClass]"
			alt="landing image"
		/>
		<LandingBackdrop :opacity="data.backdrop_opacity" />
		<div id="header" class="fixed top-0 left-0 right-0 z-40 flex items-center justify-between px-6 py-5" :class="[entranceClass, restDelayClass]">
			<a href="#" class="flex items-center">
				<img v-if="data.landing_header_logo !== ''" :src="data.landing_header_logo" alt="logo" class="h-8 object-contain" />
				<span v-else class="flex flex-col">
					<span class="text-sm font-bold uppercase tracking-widest text-white">{{ data.landing_title }}</span>
					<span v-if="data.landing_subtitle !== ''" class="text-2xs uppercase tracking-[0.3em] text-white/70">{{
						data.landing_subtitle
					}}</span>
				</span>
			</a>
			<ul class="flex items-center gap-6 list-none">
				<li v-for="link in navLinks" :key="link.id">
					<RouterLink
						v-if="link.is_built_in"
						:to="{ name: link.url }"
						class="text-xs uppercase tracking-widest text-white opacity-50 hover:opacity-100 hover:scale-125 inline-block transition-all"
					>
						{{ link.label }}
					</RouterLink>
					<a
						v-else
						:href="link.url"
						:target="link.open_in_new_tab ? '_blank' : undefined"
						:rel="link.open_in_new_tab ? 'noopener' : undefined"
						class="text-xs uppercase tracking-widest text-white opacity-50 hover:opacity-100 hover:scale-125 inline-block transition-all"
					>
						{{ link.label }}
					</a>
				</li>
			</ul>
		</div>

		<LandingIntroScreen :data="data" :effective-preset="effectivePreset" />

		<!-- The signature "two lines": fixed, full-height vertical rules — like meridians on a map — with
		     rotated-text labels anchored at a configurable height along each one. -->
		<LandingMeridianRail
			:to="{ name: 'home' }"
			origin="top"
			:line-position="exploreLinePosition"
			:label-offset="exploreOffset"
			:caption="$t('landing.meridian.explore_caption')"
			:label="exploreLabel"
			:line-animation-class="lineAnimationClass"
			:intro-delay-class="introDelayClass"
			:caption-entrance-class="captionEntranceClass"
			:label-entrance-class="labelEntranceClass"
		/>

		<LandingMeridianRail
			v-if="showContactLine"
			:to="{ name: 'contact' }"
			origin="bottom"
			:line-position="contactLinePosition"
			:label-offset="contactOffset"
			:caption="$t('landing.meridian.contact_caption')"
			:label="$t('landing.contact')"
			:line-animation-class="lineAnimationClass"
			:intro-delay-class="introDelayClass"
			:caption-entrance-class="captionEntranceClass"
			:label-entrance-class="labelEntranceClass"
		/>

		<LandingFooter
			:footer-data="data.footer"
			:links="data.links"
			:animated="effectivePreset !== 'none'"
			:no-intro-delay="!data.intro_screen_enabled"
			dim-icons-until-hover
		/>
	</div>
</template>
<script setup lang="ts">
import { computed, toRef } from "vue";
import { RouterLink } from "vue-router";
import LandingFooter from "@/v8/components/footers/LandingFooter.vue";
import LandingIntroScreen from "@/v8/components/landing/LandingIntroScreen.vue";
import LandingMeridianRail from "@/v8/components/landing/LandingMeridianRail.vue";
import LandingBackgroundImages from "@/v8/components/landing/LandingBackgroundImages.vue";
import LandingBackdrop from "@/v8/components/landing/LandingBackdrop.vue";
import { useLandingAnimation } from "@/v8/composables/landing/useLandingAnimation";
import type { LandingPreviewOrientation } from "@/v8/composables/landing/useLandingBackgroundOrientation";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	data: App.Http.Resources.GalleryConfigs.LandingPageResource;
	previewOrientation?: LandingPreviewOrientation;
}>();

const landingData = toRef(props, "data");

const { effectivePreset, introDelayClass } = useLandingAnimation(landingData);

// The two rail lines must finish growing before anything else on the page appears — background,
// header, caption, and label all wait for them, whether or not the intro splash is enabled.
// `introDelayClass` (used on the lines themselves, below) zeroes a baked-in delay straight to 0s,
// which is correct for the lines but would collapse this second stage's wait into the *same*
// instant as the lines if reused here too. These two computed classes hold that same offset in
// place: 0.13s, slightly *less* than the line-grow animation's own 0.2s duration, so this stage
// starts while the rails' ease-out tail is still finishing rather than after a dead pause once it
// technically ends (that tail is barely visible motion, so the overlap reads as continuous, not
// early); +0.15s more on top for the caption, matching its existing stagger against the label.
const restDelayClass = computed(() => (props.data.intro_screen_enabled ? "" : "meridian-after-lines"));
const captionDelayClass = computed(() => (props.data.intro_screen_enabled ? "" : "meridian-after-lines-caption"));

// Background/header reveal: the second stage of the sequence below, delayed (via the
// --animate-landingMeridianReveal* custom properties in app-v8.css) to start only once the two
// rail lines have finished growing in. `opacity-0` is the pre-animation resting state — needed
// because animation-fill-mode is 'forwards' (not 'both'), so without it these would render at
// full opacity during the delay, before their animation has actually started.
const entranceClass = computed(() => {
	switch (effectivePreset.value) {
		case "zoom_in":
			return ["opacity-0", "animate-landingMeridianRevealZoom"];
		case "slide_reveal":
		case "parallax_scroll":
			return ["opacity-0", "animate-landingMeridianRevealSlide"];
		case "none":
			return "";
		default:
			return ["opacity-0", "animate-landingMeridianRevealPop"];
	}
});

// The signature "two lines" effect: each rail's `before:` pseudo-element grows in from one end
// (transform-origin set per-rail in the template — top for the left/home rail, bottom for the
// right/contact rail) via landingMeridianLineGrow. This is stage one of the reveal; everything
// else (entranceClass above, captionEntranceClass/labelEntranceClass below) is delayed to start
// only once this finishes, via the baked delays on the --animate-landingMeridian* custom
// properties in app-v8.css.
const lineAnimationClass = computed(() => (effectivePreset.value !== "none" ? "before:scale-y-0 before:animate-landingMeridianLineGrow" : ""));

// The caption and label converge from opposite directions once the lines are done: the small
// caption drops down into place, the big label rises up into place. Independent of
// `entranceClass`/animation_preset — this is the meridian layout's own signature detail.
// Delayed via captionDelayClass/restDelayClass (see above) so they still wait for the lines to
// finish even with no intro splash to time against.
const captionEntranceClass = computed(() => ["opacity-0", "animate-landingMeridianCaptionIn", captionDelayClass.value]);
const labelEntranceClass = computed(() => ["opacity-0", "animate-landingMeridianLabelIn", restDelayClass.value]);

const navLinks = computed(() => props.data.links.filter((link) => link.placement === "nav" || link.placement === "both"));

const exploreLabel = computed(() => (props.data.cta_text !== "" ? props.data.cta_text : trans("landing.meridian.explore_label")));

// Without a contact form there's nothing for the second rail to link to, so it collapses back
// to a single, centered line rather than pointing somewhere meaningless — the configured explore
// line position is ignored in that case, since an off-center lone rail reads as a mistake, not a
// deliberate choice.
const showContactLine = computed(() => props.data.footer.is_contact_form_enabled);

// Position of each rail's label along its full-height line, as a percentage from the top
// (`top: X%` on an absolutely-positioned block, centered on that point via `translate(-50%, -50%)`).
// Clamped so the label never collapses fully into the header/footer at the extremes.
const clampOffset = (value: number): number => Math.min(95, Math.max(5, value));
const exploreOffset = computed(() => clampOffset(props.data.meridian_explore_offset));
const contactOffset = computed(() => clampOffset(props.data.meridian_contact_offset));

// Horizontal position of each rail itself, as a percentage from the left (`left: X%` on the
// fixed-position RouterLink, centered on that point via the static `-translate-x-1/2` class in
// the template — same convention as the vertical label offsets above). Reuses the same 5-95 clamp
// so a rail can't be pushed fully off-screen at the extremes.
const exploreLinePosition = computed(() => (showContactLine.value ? clampOffset(props.data.meridian_explore_line_position) : 50));
const contactLinePosition = computed(() => clampOffset(props.data.meridian_contact_line_position));
</script>
<style lang="css" scoped>
/* Needed here for the header, which still lives in this component directly - and still reaches
   the background images despite them now living in LandingBackgroundImages.vue's own template,
   because Vue tags a child component's root element with the parent's scope id too (only its
   *descendants* are opaque to the parent's scoped CSS), and the wrapper-class prop lands right on
   that root. The rails' own version of this same rule - plus the `::before`/caption-specific
   variants only they need - lives in LandingMeridianRail.vue alongside the elements it targets.
   Applied instead of .no-intro-delay so the reveal still starts at the same 0.13s offset even with
   no intro splash to time against - only the rails themselves (in the child) get to start at 0s. */
.meridian-after-lines {
	animation-delay: 0.13s !important;
}
</style>
