<div id="menu_wrap" class="animate animate-down">
    <div id="menu">
            <ul class="menu">
                @foreach($menus as $menu)
                    <li class="menu-item">
                        <a href="{{ url($menu->link) }}">{{ $menu->menu_title }}</a>
                    </li>
                @endforeach
            </ul>
    </div><!-- menu -->
</div><!-- wrap -->
