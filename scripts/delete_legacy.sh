#!/usr/bin/sh

set -e

rm -rf app/Legacy/V1
rm -rf app/Legacy/Actions/Settings
rm -fr app/Livewire
rm -fr app/View/Components/Gallery
rm -fr config/livewire.php
rm -fr resources/livewireJs
rm -fr resources/views/components/context-menu
rm -fr resources/views/components/forms
rm -fr resources/views/components/header
rm -fr resources/views/components/help
rm -fr resources/views/components/icons
rm -fr resources/views/components/layout
rm -fr resources/views/components/leftbar
rm -fr resources/views/components/maintenance
rm -fr resources/views/components/webautn
rm -fr resources/views/components/footer-landing.blade.php
rm -fr resources/views/components/footer.blade.php
rm -fr resources/views/components/notifications.blade.php
rm -fr resources/views/components/shortcuts.blade.php
rm -fr resources/views/components/update-status.blade.php
rm -fr resources/views/livewire
rm -fr resources/views/vendor/livewire
rm -fr resources/views/vendor/pagination
rm -fr resources/views/access-permissions.blade.php
rm -fr resources/views/diagnostics.blade.php
rm -fr resources/views/frontend.blade.php
rm -fr resources/views/jobs.blade.php
rm -fr resources/views/list.blade.php
rm -fr resources/views/landing.blade.php
rm -fr tests/Feature_v1
rm -fr routes/api_v1.php
rm -fr routes/web_v1.php
rm -fr routes/web-admin-v1.php

sed -i "\#^.*login_required_v1#d" ./app/Http/Kernel.php

sed -i '11d;50,56d;60,64d;68,75d' ./app/Providers/RouteServiceProvider.php
sed -i '104,111d' phpstan.neon

		
