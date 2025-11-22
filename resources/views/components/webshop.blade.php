@if($enable)
	@if($with_mollie)
		<script src="https://js.mollie.com/v1/mollie.js" async></script>
	@endif
	{{-- @if($with_stripe)
		<script src="https://js.stripe.com/clover/stripe.js" async></script>
	@endif --}}
@endif