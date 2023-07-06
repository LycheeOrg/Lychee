<div id="lychee_sidebar_container" @class(["hflex-item-rigid",
"active" => $isOpen])>
@livewire('modules.sidebar.' . $mode)
</div>