<div class="basicModal__content">
	<form class="token">
		<div class="input-group stacked">
			<label for="token-dialog-token">Token</label>
			<input id="token-dialog-token"
				@if($isDisabled)
					{{-- token is completely disabled --}}
					value="{{ __('lychee.DISABLED_TOKEN_STATUS_MSG') }}"
					disabled="disabled"
				@elseif($isHidden)
					value="{{ __('lychee.TOKEN_NOT_AVAILABLE') }}"
					disabled="disabled"
				@else
					value="{{ $token }}"
				@endif
				type="text"
				readonly="readonly"
				placeholder="{{ __("lychee.TOKEN_WAIT") }}"
			/>

			<div class="button-group">
				<a id="button_reset_token"
					class='button'
					title='{{ __('lychee.RESET') }}'
					wire:click='resetToken'
					>
					<x-icons.iconic class='ionicons' icon='reload' />
				</a>
				<a id="button_copy_token"
					class='button'
					title='{{ __('lychee.URL_COPY_TO_CLIPBOARD') }}'
					@if($isDisabled || $isHidden)
					style='display:none;'
					@endif
					>
					<x-icons.iconic class='ionicons' icon='copy' />
				</a>
				<a id="button_disable_token"
					class='button'
					tile='{{ __('lychee.DISABLE_TOKEN_TOOLTIP') }}'
					wire:click='disableToken'
					@if($isDisabled)
					style='display:none;'
					@endif
					>
					<x-icons.iconic class='ionicons' icon='ban' />
				</a>
			</div>
		</div>
	</form>
</div>