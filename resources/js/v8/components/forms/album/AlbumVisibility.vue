<template>
	<UCard class="text-sm p-4 xl:px-9 w-full xl:basis-1/3 xl:min-w-0" :ui="{ body: 'p-0' }">
		<form>
			<div class="h-12">
				<USwitch v-model="is_public" input-id="pp_dialog_public_check" class="ltr:mr-2 rtl:ml-2 translate-y-1" @change="save" />
				<label for="pp_dialog_public_check" class="font-bold">{{ $t("dialogs.visibility.public") }}</label>
				<p class="my-1.5">{{ $t("dialogs.visibility.public_expl") }}</p>
			</div>
			<Collapse v-if="albumStore.config?.is_base_album" :when="is_public">
				<div
					class="relative h-12 my-4 ltr:pl-9 rtl:pr-9 transition-color duration-300"
					:class="is_public ? 'text-highlighted' : 'text-muted'"
				>
					<USwitch
						v-model="grants_full_photo_access"
						input-id="pp_dialog_full_check"
						class="ltr:-ml-10 ltr:mr-2 rtl:-mr-10 rtl:ml-2 translate-y-1"
						@change="save"
					/>
					<label class="font-bold" for="pp_dialog_full_check">{{ $t("dialogs.visibility.full") }}</label>
					<p class="my-1.5">{{ $t("dialogs.visibility.full_expl") }}</p>
				</div>
				<div
					class="relative h-12 my-4 ltr:pl-9 rtl:pr-9 transition-color duration-300"
					:class="is_public ? 'text-highlighted' : 'text-muted'"
				>
					<USwitch
						v-model="is_link_required"
						input-id="pp_dialog_link_check"
						class="ltr:-ml-10 ltr:mr-2 rtl:-mr-10 rtl:ml-2 translate-y-1"
						@change="save"
					/>
					<label class="font-bold" for="pp_dialog_link_check">{{ $t("dialogs.visibility.hidden") }}</label>
					<p class="my-1.5">{{ $t("dialogs.visibility.hidden_expl") }}</p>
				</div>
				<div
					class="relative h-12 my-4 ltr:pl-9 rtl:pr-9 transition-color duration-300"
					:class="is_public ? 'text-highlighted' : 'text-muted'"
				>
					<USwitch
						v-model="grants_download"
						input-id="pp_dialog_downloadable_check"
						class="ltr:-ml-10 ltr:mr-2 rtl:-mr-10 rtl:ml-2 translate-y-1"
						@change="save"
					/>
					<label class="font-bold" for="pp_dialog_downloadable_check">{{ $t("dialogs.visibility.downloadable") }}</label>
					<p class="my-1.5">{{ $t("dialogs.visibility.downloadable_expl") }}</p>
				</div>
				<div
					v-if="is_se_enabled || is_se_preview_enabled"
					class="relative h-12 my-4 ltr:pl-9 rtl:pr-9 transition-color duration-300"
					:class="is_public ? 'text-highlighted' : 'text-muted'"
				>
					<USwitch
						v-model="grants_upload"
						input-id="pp_dialog_upload_check"
						:disabled="!is_se_enabled"
						class="ltr:-ml-10 ltr:mr-2 rtl:-mr-10 rtl:ml-2 translate-y-1"
						color="error"
						@change="save"
					/>
					<label class="font-bold inline-flex items-center" for="pp_dialog_upload_check">
						{{ $t("dialogs.visibility.upload") }} <SETag v-if="is_se_preview_enabled" class="ml-2" />
					</label>
					<p class="my-1.5" v-html="$t('dialogs.visibility.upload_expl')"></p>
				</div>
				<div
					v-if="!can_pasword_protect && is_password_required"
					class="relative my-4 ltr:pl-9 rtl:pr-9 transition-color duration-300"
					:class="is_public ? 'text-highlighted' : 'text-muted'"
				>
					<USwitch
						v-model="is_password_required"
						input-id="pp_dialog_password_check_2"
						class="ltr:-ml-10 ltr:mr-2 rtl:-mr-10 rtl:ml-2 translate-y-1"
						color="error"
						@change="save"
					/>
					<label class="font-bold inline-flex items-center gap-2" for="pp_dialog_password_check_2">
						<span class="border-b border-dashed border-error">{{ $t("dialogs.visibility.password_prot") }}</span>
						<UIcon name="prime:exclamation-triangle" class="text-error" />
					</label>
					<p class="mt-1.5 mb-4 text-error/80" v-html="$t('dialogs.visibility.password_prop_not_compatible')"></p>
				</div>
				<div
					v-else-if="can_pasword_protect"
					class="relative my-4 ltr:pl-9 rtl:pr-9 transition-color duration-300"
					:class="is_public ? 'text-highlighted' : 'text-muted'"
				>
					<USwitch
						v-model="is_password_required"
						input-id="pp_dialog_password_check"
						class="ltr:-ml-10 ltr:mr-2 rtl:-mr-10 rtl:ml-2 translate-y-1"
						@change="save"
					/>
					<label class="font-bold" for="pp_dialog_password_check">{{ $t("dialogs.visibility.password_prot") }}</label>
					<p class="mt-1.5 mb-4">{{ $t("dialogs.visibility.password_prot_expl") }}</p>
					<UFormField v-if="is_password_required" :label="$t('dialogs.visibility.password')">
						<InputPassword id="password" v-model="password" autocomplete="new-password" @change="save" />
					</UFormField>
				</div>
			</Collapse>
			<template v-if="albumStore.config?.is_base_album">
				<hr class="block mt-8 mb-8 w-full border-t border-solid border-default" />
				<div class="relative h-12 my-4 transition-color duration-300">
					<USwitch v-model="is_nsfw" input-id="pp_dialog_nsfw_check" class="ltr:mr-2 rtl:ml-2 translate-y-1" color="error" @change="save" />
					<label for="pp_dialog_nsfw_check" class="font-bold" :class="is_nsfw ? ' text-red-700' : ''">
						{{ $t("dialogs.visibility.nsfw") }}
					</label>
					<p class="my-1.5">{{ $t("dialogs.visibility.nsfw_expl") }}</p>
				</div>
			</template>
		</form>
	</UCard>
</template>
<script setup lang="ts">
import { ref } from "vue";
import InputPassword from "@/v8/components/forms/basic/InputPassword.vue";
import AlbumService, { UpdateProtectionPolicyData } from "@/services/album-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { Collapse } from "vue-collapsed";
import { trans } from "laravel-vue-i18n";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import SETag from "@/v8/components/icons/SETag.vue";
import { useAlbumStore } from "@/stores/AlbumState";

const albumStore = useAlbumStore();

const toast = useAppToast();

const is_public = ref<boolean>(albumStore.album?.policy.is_public ?? false);
const is_link_required = ref<boolean>(albumStore.album?.policy.is_link_required ?? false);
const is_nsfw = ref<boolean>(albumStore.album?.policy.is_nsfw ?? false);
const grants_full_photo_access = ref<boolean>(albumStore.album?.policy.grants_full_photo_access ?? false);
const grants_download = ref<boolean>(albumStore.album?.policy.grants_download ?? false);
const is_password_required = ref<boolean>(albumStore.album?.policy.is_password_required ?? false);
const password = ref<string>("");
const can_pasword_protect = ref<boolean>(albumStore.album?.rights.can_pasword_protect ?? false);
const grants_upload = ref<boolean>(albumStore.album?.policy.grants_upload ?? false);

const lycheeStore = useLycheeStateStore();
const { is_se_enabled, is_se_preview_enabled } = storeToRefs(lycheeStore);

function save() {
	const data: UpdateProtectionPolicyData = {
		album_id: <string>albumStore.albumId,
		is_public: is_public.value,
		is_link_required: is_link_required.value,
		is_nsfw: is_nsfw.value,
		grants_full_photo_access: grants_full_photo_access.value,
		grants_download: grants_download.value,
		grants_upload: grants_upload.value,
		password: is_password_required.value ? password.value : undefined,
	};

	AlbumService.updateProtectionPolicy(data).then(() => {
		toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("dialogs.visibility.visibility_updated"), life: 3000 });
		AlbumService.clearCache(albumStore.albumId);
		if (albumStore.config?.is_model_album) {
			AlbumService.clearCache(albumStore.modelAlbum?.parent_id);
		} else {
			AlbumService.clearAlbums();
		}
	});
}
</script>
