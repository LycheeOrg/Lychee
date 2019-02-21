<div id="menu_wrap" class="animate animate-down">
    <div id="menu">
        {{--<div class="menu-menu-1-container">--}}
        {{--<div class="">--}}
            {{--<ul id="menu-menu-1" class="menu">--}}
            <ul class="menu">
                {{--<li id="menu-item-176" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-176"><a href="https://thomasheaton.co.uk/contact/">Contact</a></li>--}}
                @foreach($menus as $menu)
                    <li class="menu-item">
                        <a href="{{ $menu->link }}">{{ $menu->menu_title }}</a>
                    </li>
                @endforeach
            </ul>
        {{--</div>--}}
    </div><!-- menu -->
</div><!-- wrap -->
