<div id="lychee_sidebar"
    x-data="photoFormPanel()"
    x-on:photo-updated.window="refreshForm($store.photo)"
    class="border-t border-solid border-primary-500 text-neutral-200 w-full">
    <form class="w-full flex justify-center">
        <div
            class="w-1/2 flex justify-center flex-wrap
            text-neutral-200 text-sm
            p-9 sm:p-4 xl:px-9 max-sm:w-full sm:min-w-[32rem] flex-shrink-0">
            <div class="mb-4 mt-14 w-full">
                <p class="font-bold">{{ __('lychee.PHOTO_SET_TITLE') }}</p>
                <p class="text-text-main-400">{{ __('lychee.PHOTO_NEW_TITLE') }}</p>
                <x-forms.inputs.text x-model="title"
                 />
            </div>
            <div class="my-4 w-full">
                <p class="font-bold">{{ __('lychee.PHOTO_SET_DESCRIPTION') }}</p>
                <p class="text-text-main-400">{{ __('lychee.PHOTO_NEW_DESCRIPTION') }}</p>
                <x-forms.textarea class="w-full h-52" x-model="description"
                ></x-forms.textarea>
            </div>
            <div class="my-4 w-full">
                <p class="font-bold">{{ __('lychee.PHOTO_SET_TAGS')}}</p>
                <p class="text-text-main-400">{{ __('lychee.PHOTO_NEW_TAGS') }}</p>
                <x-forms.inputs.text x-model="tagsWithComma"
                 />
            </div>
            <div class="my-4 w-full">
                <p class="font-bold">{{ __('lychee.PHOTO_SET_CREATED_AT') }}</p>
                <p class="text-text-main-400">{{ __('lychee.PHOTO_NEW_CREATED_AT') }}</p>
                <x-forms.inputs.date x-model="uploadDate"
                 />
            </div>
            <div class="my-4 w-full">
                <p><span class="font-bold">{{ __('lychee.SET_LICENSE') }}</span>
                <x-forms.dropdown class="mx-2" :options="$this->licenses" id="licenses_dialog_select"
                    x-model="license"
                    />
                </p>
            </div>
            <x-forms.buttons.action class="rounded w-full" x-on:click="updatePhoto"
                >{{ __('lychee.SAVE') }}</x-forms.buttons.action>
        </div>
    </form>
</div>
