<template>
	<div
		v-if="data.intro_screen_enabled"
		id="intro"
		:class="{ hidden: !introVisible, 'animate-landingIntroFadeOut': effectivePreset !== 'none' }"
		class="z-50 bg-black fixed flex align-middle justify-center left-0 right-0 top-0 bottom-0"
	>
		<div id="intro_content" class="self-center">
			<img
				v-if="data.landing_logo !== ''"
				id="landing-title-logo"
				:src="data.landing_logo"
				alt="logo"
				class="max-h-32 max-w-xs object-contain"
				:class="{ 'animate-landingIntroPopIn': effectivePreset !== 'none' }"
			/>
			<template v-else>
				<h1
					class="text-center text-2xl uppercase font-extralight text-white"
					:class="{ 'animate-landingIntroPopIn': effectivePreset !== 'none' }"
				>
					{{ data.landing_title }}
				</h1>
				<h2>
					<span
						class="text-center text-base uppercase font-extralight block text-white"
						:class="{ 'animate-landingIntroPopIn': effectivePreset !== 'none' }"
					>
						{{ data.landing_subtitle }}
					</span>
				</h2>
			</template>
		</div>
	</div>
</template>
<script setup lang="ts">
import { onMounted, ref } from "vue";

const props = defineProps<{
	data: App.Http.Resources.GalleryConfigs.LandingPageResource;
	effectivePreset: App.Enum.LandingAnimationPreset;
}>();

const introVisible = ref(true);

onMounted(() => {
	if (props.data.intro_screen_enabled) {
		setTimeout(() => (introVisible.value = false), 4000);
	} else {
		introVisible.value = false;
	}
});
</script>
