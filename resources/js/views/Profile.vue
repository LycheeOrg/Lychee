<template>
	<Toolbar class="w-full border-0">
		<template #start>
			<router-link :to="{ name: 'gallery' }">
				<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="" />
			</router-link>
		</template>

		<template #center>
			{{ $t("lychee.PROFILE") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<SetLogin />
	<!-- <Card class=""> -->
	<!-- <div class="flex flex-wrap flex-row flex-shrink w-full justify-start align-top">
				<template v-for="album in props.albums">
					<AlbumThumb
						:album="album"
						:cover_id="null"
						:asepct_ratio="props.config.album_thumb_css_aspect_ratio"
						:album_subtitle_type="props.config.album_subtitle_type"
					/>
				</template>
			</div> -->
	<!-- <div class="settings_view max-w-xl text-text-main-400 text-sm mx-auto">
			<livewire:forms.profile.set-login />
			<livewire:forms.profile.oauth />
			@if($are_notification_active)
			<div>
				<p>
					{{ __('lychee.USER_EMAIL_INSTRUCTION') }}
				</p>
			</div>
			<livewire:forms.profile.set-email />
			@endif
			<livewire:forms.profile.second-factor />
		</div>
	</div> -->
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import SetLogin from "@/components/forms/profile/SetLogin.vue";
// import Card from 'primevue/card';
</script>
