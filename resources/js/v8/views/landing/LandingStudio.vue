<template>
	<!-- This is necessary for the preview -->
	<div class="fixed inset-0 bg-black text-white flex flex-col overflow-hidden">
		<LandingBackgroundImages
			:landscape="data.landing_background_landscape"
			:portrait="data.landing_background_portrait"
			:preview-orientation="previewOrientation"
			:visible="hasBackground"
		/>
		<LandingBackdrop :opacity="data.backdrop_opacity" />

		<WebauthnModal v-if="!isPreview" @logged-in="handleLoggedIn" />

		<div id="logo" class="relative p-6">
			<img v-if="data.landing_header_logo !== ''" :src="data.landing_header_logo" alt="logo" class="h-10 object-contain" />
		</div>

		<!-- md:contents drops this wrapper from rendering at md+ (its children become direct flex
		     items of <main> again, unchanged from the side-docked desktop layout below). Only
		     needed below md: it centers the hero content + login panel together as one group in
		     the space between the logo and footer, instead of each managing its own vertical
		     space independently - which is what left a large gap above the panel when the hero
		     had nothing to show (no about text) but still claimed a full share of that space. -->
		<div class="flex flex-1 flex-col items-center justify-center gap-6 md:contents">
			<div class="relative flex flex-col items-center justify-center text-center px-6 md:flex-1" :class="[entranceClass, heroPaddingClass]">
				<p v-if="showAbout" class="max-w-lg text-sm text-muted prose prose-invert prose-sm" v-html="data.about_text" />
			</div>

			<div :class="[loginWrapperClass, loginEntranceClass]">
				<UCard class="w-full shadow-2xl">
					<template #header>
						<h2 class="text-lg font-bold uppercase tracking-wide text-center">{{ data.landing_title }}</h2>
						<p v-if="data.landing_subtitle !== ''" class="text-xs mt-1 text-center">{{ data.landing_subtitle }}</p>
					</template>
					<!-- Preview (admin settings panel) renders a static mockup instead of the real form: mounting
					     LoginForm there would fire a live oauth-providers fetch on every preview, and - in an
					     oauth-only setup with no basic auth/webauthn - auto-redirect the admin out of Settings
					     via LoginForm's redirectToOauth(). -->
					<template v-if="isPreview">
						<div class="flex flex-col gap-4 text-sm">
							<UFormField :label="$t('dialogs.login.username')">
								<UInput disabled class="w-full" />
							</UFormField>
							<UFormField :label="$t('dialogs.login.password')">
								<UInput disabled type="password" class="w-full" />
							</UFormField>
							<UButton color="primary" variant="solid" disabled class="w-full justify-center font-bold">
								{{ $t("dialogs.login.signin") }}
							</UButton>
						</div>
					</template>
					<template v-else>
						<LoginForm hide-remember-me @logged-in="handleLoggedIn" />
						<div v-if="is_registration_enabled && is_basic_auth_enabled" class="text-center mt-4">
							<RouterLink to="/register" class="text-highlighted text-sm font-bold hover:underline">
								{{ $t("profile.register.signup") }}
							</RouterLink>
						</div>
					</template>
					<RouterLink :to="{ name: 'home' }" class="mt-4 block text-center text-xs uppercase text-muted hover:text-white transition-colors">
						{{ $t("landing.view_public_gallery") }}
					</RouterLink>
				</UCard>
			</div>
		</div>

		<!-- Unlike Classic/Meridian/Portfolio, Studio's footer sits right below the login panel in
		     the same viewport (not a separate scroll-in section), so it should settle in step with
		     the rest of the page instead of trailing behind on the shared 3s intro delay. -->
		<LandingFooter :footer-data="data.footer" :links="data.links" :animated="effectivePreset !== 'none'" no-intro-delay dim-icons-until-hover />

		<LandingIntroScreen :data="data" :effective-preset="effectivePreset" />
	</div>
</template>
<script setup lang="ts">
import { computed, toRef } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import LandingIntroScreen from "@/v8/components/landing/LandingIntroScreen.vue";
import LandingBackgroundImages from "@/v8/components/landing/LandingBackgroundImages.vue";
import LandingBackdrop from "@/v8/components/landing/LandingBackdrop.vue";
import LoginForm from "@/v8/components/forms/auth/LoginForm.vue";
import WebauthnModal from "@/v8/components/modals/WebauthnModal.vue";
import { useLandingAnimation } from "@/v8/composables/landing/useLandingAnimation";
import type { LandingPreviewOrientation } from "@/v8/composables/landing/useLandingBackgroundOrientation";
import { useAdvisoryModal } from "@/composables/modals/useAdvisoryModal";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useUserStore } from "@/stores/UserState";
import LandingFooter from "@/v8/components/footers/LandingFooter.vue";

const props = defineProps<{
	data: App.Http.Resources.GalleryConfigs.LandingPageResource;
	previewOrientation?: LandingPreviewOrientation;
}>();

// Landing.vue (the real, standalone landing page) never passes previewOrientation; LandingConfig.vue's
// live admin preview always does. Used to swap the interactive login form for a static mockup there.
const isPreview = computed(() => props.previewOrientation !== undefined);

const landingData = toRef(props, "data");

const { effectivePreset } = useLandingAnimation(landingData);

const router = useRouter();
const lycheeStore = useLycheeStateStore();
const userStore = useUserStore();
const { advisoryCheck } = useAdvisoryModal();
const { is_registration_enabled, is_basic_auth_enabled } = storeToRefs(lycheeStore);

async function handleLoggedIn() {
	await Promise.allSettled([lycheeStore.load(), userStore.refresh()]);
	advisoryCheck();
	router.push({ name: "gallery" });
}

const entranceClass = computed(() => {
	switch (effectivePreset.value) {
		case "zoom_in":
			return "animate-landingZoomReveal";
		case "slide_reveal":
		case "parallax_scroll":
			return "animate-landingSlideReveal";
		case "none":
			return "";
		default:
			return "animate-landingIntroPopIn";
	}
});

const hasBackground = computed(() => props.data.landing_background_landscape !== "" || props.data.landing_background_portrait !== "");

const showAbout = computed(() => props.data.about_enabled && props.data.about_text !== "");

// "center" pins the login panel in normal flow (same treatment as narrow screens always get);
// anything else ("side") docks it to the reading-direction edge on md+ screens.
const isSidePosition = computed(() => props.data.login_position !== "center");

// The panel's edge offset (below) is a % of the viewport, not a fixed rem value, so it keeps
// growing on wide screens instead of reading as pinned in the corner - this reserve tracks it
// (card width + offset) for the same reason. Not needed when the panel is centered in normal
// flow instead.
const heroPaddingClass = computed(() => (isSidePosition.value ? "md:ltr:pr-[calc(24rem_+_15%)] md:rtl:pl-[calc(24rem_+_15%)]" : ""));

const loginWrapperClass = computed(() => {
	const base = "relative z-10 w-full max-w-sm mx-auto px-6 pb-10 md:pb-0";
	if (!isSidePosition.value) {
		return base;
	}
	return `${base} md:px-0 md:max-w-none md:mx-0 md:w-96 md:absolute md:inset-y-0 md:flex md:items-center md:ltr:right-[15%] md:rtl:left-[15%]`;
});

// Side-docked panels get a bespoke horizontal slide from their own edge (ltr:from-right,
// rtl:from-left) under the "slide reveal"/"parallax scroll" presets, matching the direction
// they're pinned to - landingSlideReveal's generic vertical rise wouldn't read as "coming from
// the edge" the way this does. Centered panels have no edge to slide from, so they just reuse
// the same generic entrance as the hero content for every preset.
const loginEntranceClass = computed(() => {
	if (!isSidePosition.value) {
		return entranceClass.value;
	}
	switch (effectivePreset.value) {
		case "slide_reveal":
		case "parallax_scroll":
			return "ltr:animate-landingLoginSlideInFromRight rtl:animate-landingLoginSlideInFromLeft";
		default:
			return entranceClass.value;
	}
});
</script>
