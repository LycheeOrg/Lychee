#!/usr/bin/sh

set -e

rm -rf app/Legacy/V1
rm -rf app/Legacy/Actions/Settings
rm -fr app/Livewire
rm -fr tests/Feature_v1
rm -fr app/View/Components/Gallery
sed -i "\#^.*login_required_v1#d" ./app/Http/Kernel.php


		
