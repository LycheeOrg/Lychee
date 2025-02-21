<template>
	<Card class="text-sm p-4 xl:px-9 sm:min-w-[32rem]">
		<template #content>
			<form>
				<div class="h-12">
					<ToggleSwitch v-model="is_public" class="mr-2 translate-y-1" @change="save" />
					<label for="pp_dialog_public_check" class="font-bold">{{ $t("dialogs.visibility.public") }}</label>
					<p class="my-1.5">{{ $t("dialogs.visibility.public_expl") }}</p>
				</div>
				<Collapse v-if="props.config.is_base_album" :when="is_public">
					<div
						class="relative h-12 my-4 pl-9 transition-color duration-300"
						:class="is_public ? 'text-muted-color-emphasis' : 'text-muted-color'"
					>
						<ToggleSwitch
							input-id="pp_dialog_full_check"
							v-model="grants_full_photo_access"
							class="-ml-10 mr-2 translate-y-1"
							@change="save"
						/>
						<label class="font-bold" for="pp_dialog_full_check">{{ $t("dialogs.visibility.full") }}</label>
						<p class="my-1.5">{{ $t("dialogs.visibility.full_expl") }}</p>
					</div>
					<div
						class="relative h-12 my-4 pl-9 transition-color duration-300"
						:class="is_public ? 'text-muted-color-emphasis' : 'text-muted-color'"
					>
						<ToggleSwitch input-id="pp_dialog_link_check" v-model="is_link_required" class="-ml-10 mr-2 translate-y-1" @change="save" />
						<label class="font-bold" for="pp_dialog_link_check">{{ $t("dialogs.visibility.hidden") }}</label>
						<p class="my-1.5">{{ $t("dialogs.visibility.hidden_expl") }}</p>
					</div>
					<div
						class="relative h-12 my-4 pl-9 transition-color duration-300"
						:class="is_public ? 'text-muted-color-emphasis' : 'text-muted-color'"
					>
						<ToggleSwitch
							input-id="pp_dialog_downloadable_check"
							v-model="grants_download"
							class="-ml-10 mr-2 translate-y-1"
							@change="save"
						/>
						<label class="font-bold" for="pp_dialog_downloadable_check">{{ $t("dialogs.visibility.downloadable") }}</label>
						<p class="my-1.5">{{ $t("dialogs.visibility.downloadable_expl") }}</p>
					</div>
					<div
						v-if="!can_pasword_protect && is_password_required"
						class="relative my-4 pl-9 transition-color duration-300"
						:class="is_public ? 'text-muted-color-emphasis' : 'text-muted-color'"
					>
						<ToggleSwitch
							input-id="pp_dialog_password_check_2"
							v-model="is_password_required"
							class="-ml-10 mr-2 translate-y-1 group"
							:pt:slider:class="'group-has-checked:bg-danger-700'"
							@change="save"
						/>
						<label class="font-bold inline-flex items-center gap-2" for="pp_dialog_password_check_2">
							<span class="border-b border-dashed border-danger-700">{{ $t("dialogs.visibility.password_prot") }}</span>
							<i class="pi pi-exclamation-triangle text-danger-700" />
						</label>
						<p class="mt-1.5 mb-4 text-danger-600/80" v-html="$t('dialogs.visibility.password_prop_not_compatible')"></p>
					</div>
					<div
						v-else-if="can_pasword_protect"
						class="relative my-4 pl-9 transition-color duration-300"
						:class="is_public ? 'text-muted-color-emphasis' : 'text-muted-color'"
					>
						<ToggleSwitch
							input-id="pp_dialog_password_check"
							v-model="is_password_required"
							class="-ml-10 mr-2 translate-y-1"
							@change="save"
						/>
						<label class="font-bold" for="pp_dialog_password_check">{{ $t("dialogs.visibility.password_prot") }}</label>
						<p class="mt-1.5 mb-4">{{ $t("dialogs.visibility.password_prot_expl") }}</p>
						<FloatLabel v-if="is_password_required">
							<InputPassword id="password" v-model="password" autocomplete="new-password" @change="save" />
							<label for="password">{{ $t("dialogs.visibility.password") }}</label>
						</FloatLabel>
					</div>
				</Collapse>
			</form>
			<template v-if="props.config.is_base_album">
				<hr class="block mt-8 mb-8 w-full border-t border-solid border-surface-600" />
				<form>
					<div class="relative h-12 my-4 transition-color duration-300">
						<ToggleSwitch
							input-id="pp_dialog_nsfw_check"
							v-model="is_nsfw"
							class="mr-2 translate-y-1"
							style="
								--p-toggleswitch-checked-background: var(--p-red-800);
								--p-toggleswitch-checked-hover-background: var(--p-red-900);
								--p-toggleswitch-hover-background: var(--p-red-900);
							"
							@change="save"
						/>
						<label for="pp_dialog_nsfw_check" class="font-bold" :class="is_nsfw ? ' text-red-700' : ''">{{
							$t("dialogs.visibility.nsfw")
						}}</label>
						<p class="my-1.5">{{ $t("dialogs.visibility.nsfw_expl") }}</p>
					</div>
				</form>
			</template>
		</template>
	</Card>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Card from "primevue/card";
import ToggleSwitch from "primevue/toggleswitch";
import FloatLabel from "primevue/floatlabel";
import InputPassword from "@/components/forms/basic/InputPassword.vue";
import AlbumService, { UpdateProtectionPolicyData } from "@/services/album-service";
import { useToast } from "primevue/usetoast";
import { Collapse } from "vue-collapsed";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.SmartAlbumResource | App.Http.Resources.Models.TagAlbumResource;
	config: App.Http.Resources.GalleryConfigs.AlbumConfig;
}>();
const toast = useToast();
const albumId = ref<string>(props.album.id);
const is_public = ref<boolean>(props.album.policy.is_public);
const is_link_required = ref<boolean>(props.album.policy.is_link_required);
const is_nsfw = ref<boolean>(props.album.policy.is_nsfw);
const grants_full_photo_access = ref<boolean>(props.album.policy.grants_full_photo_access);
const grants_download = ref<boolean>(props.album.policy.grants_download);
const is_password_required = ref<boolean>(props.album.policy.is_password_required);
const password = ref<string>("");
const can_pasword_protect = ref<boolean>(props.album.rights.can_pasword_protect);

function save() {
	const data: UpdateProtectionPolicyData = {
		album_id: albumId.value,
		is_public: is_public.value,
		is_link_required: is_link_required.value,
		is_nsfw: is_nsfw.value,
		grants_full_photo_access: grants_full_photo_access.value,
		grants_download: grants_download.value,
		password: is_password_required.value ? password.value : undefined,
	};

	AlbumService.updateProtectionPolicy(data).then(() => {
		toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("dialogs.visibility.visibility_updated"), life: 3000 });
		AlbumService.clearCache(albumId.value);
		if (props.config.is_model_album) {
			// @ts-expect-error
			AlbumService.clearCache(props.album.parent_id);
		} else {
			AlbumService.clearAlbums();
		}
	});
}
</script>
