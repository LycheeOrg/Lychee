<template>
	<UFooter v-if="footerData" id="footer">
		<div class="w-full flex flex-col justify-end flex-wrap align-bottom self-end text-center py-5 px-0 text-3xs">
			<ContactForm v-if="isContactFormVisible" class="w-full text-left mt-4 text-base" />

			<div v-if="footerData.footer_show_social_media" id="home_socials" class="w-full text-muted text-base space-x-2">
				<SocialMediaLinks :footer-data="footerData" link-class="inline-block" />
			</div>
			<p
				v-if="footerData.footer_show_copyright && footerData.copyright !== ''"
				class="home_copyright w-full uppercase text-muted leading-6 font-normal"
			>
				{{ footerData.copyright }}
			</p>
			<p
				v-if="footerData.footer_additional_text !== ''"
				class="personal_text w-full text-muted leading-6 font-normal"
				v-html="footerData.footer_additional_text"
			></p>
			<p v-if="is_contact_form_enabled && !isContactFormVisible" class="contact_form_link w-full uppercase text-muted leading-6 font-normal">
				<a rel="noopener noreferrer" target="_blank" :href="Constants.BASE_URL + '/contact'" class="underline">
					{{ footerData.contact_header ? footerData.contact_header : $t("contact.title") }}
				</a>
			</p>
			<p class="hosted_by w-full uppercase text-muted leading-6 font-normal" v-if="!is_white_label_enabled">
				<a rel="noopener noreferrer" target="_blank" href="https://lycheeorg.dev" tabindex="-1" class="underline">
					{{ $t("landing.Powered_by_Lychee") }}
				</a>
			</p>
		</div>
	</UFooter>
</template>
<script setup lang="ts">
import InitService from "@/services/init-service";
import ContactForm from "@/v8/components/forms/contact/ContactForm.vue";
import SocialMediaLinks from "@/v8/components/footers/SocialMediaLinks.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { computed, ref } from "vue";
import Constants from "@/services/constants";

const props = defineProps<{
	context?: "gallery" | "album";
}>();

const lycheeStore = useLycheeStateStore();
const { is_white_label_enabled, is_contact_form_enabled, is_contact_form_enabled_on_gallery, is_contact_form_enabled_on_album } =
	storeToRefs(lycheeStore);

const isContactFormVisible = computed(() => {
	if (props.context === "gallery") {
		return is_contact_form_enabled_on_gallery.value;
	}
	if (props.context === "album") {
		return is_contact_form_enabled_on_album.value;
	}

	return false;
});

const footerData = ref<App.Http.Resources.GalleryConfigs.FooterConfig | undefined>(undefined);
InitService.fetchFooter().then((data) => {
	footerData.value = data.data;
});
</script>
