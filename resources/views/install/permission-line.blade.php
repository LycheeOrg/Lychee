<span class="perm float-right">
@if($perm[1] & 1)
    <i class="fa fa-fw fa-exclamation-circle error"></i>
@else
    <i class="fa fa-fw fa-check-circle-o success"></i>
@endif
{{ $perm[0] }}</span>