<?php

declare(strict_types=1);

/*
 * This file is part of Laravel Markdown.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Enable View Integration
    |--------------------------------------------------------------------------
    |
    | This option specifies if the view integration is enabled so you can write
    | markdown views and have them rendered as html. The following extensions
    | are currently supported: ".md", ".md.php", and ".md.blade.php". You may
    | disable this integration if it is conflicting with another package.
    |
    | Default: true
    |
    */

    'views' => true,

    /*
    |--------------------------------------------------------------------------
    | CommonMark Extensions
    |--------------------------------------------------------------------------
    |
    | This option specifies what extensions will be automatically enabled.
    | Simply provide your extension class names here.
    |
    | Default: []
    |
    */

    'extensions' => [],

    /*
    |--------------------------------------------------------------------------
    | Renderer Configuration
    |--------------------------------------------------------------------------
    |
    | This option specifies an array of options for rendering HTML.
    |
    | Default: [
    |              'block_separator' => "\n",
    |              'inner_separator' => "\n",
    |              'soft_break'      => "\n",
    |          ]
    |
    */

    'renderer' => [
        'block_separator' => "\n",
        'inner_separator' => "\n",
        'soft_break'      => "\n",
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable Em Tag Parsing
    |--------------------------------------------------------------------------
    |
    | This option specifies if `<em>` parsing is enabled.
    |
    | Default: true
    |
    */

    'enable_em' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable Strong Tag Parsing
    |--------------------------------------------------------------------------
    |
    | This option specifies if `<strong>` parsing is enabled.
    |
    | Default: true
    |
    */

    'enable_strong' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable Asterisk Parsing
    |--------------------------------------------------------------------------
    |
    | This option specifies if `*` should be parsed for emphasis.
    |
    | Default: true
    |
    */

    'use_asterisk' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable Underscore Parsing
    |--------------------------------------------------------------------------
    |
    | This option specifies if `_` should be parsed for emphasis.
    |
    | Default: true
    |
    */

    'use_underscore' => true,

    /*
    |--------------------------------------------------------------------------
    | HTML Input
    |--------------------------------------------------------------------------
    |
    | This option specifies how to handle untrusted HTML input.
    |
    | Default: 'strip'
    |
    */

    'html_input' => 'strip',

    /*
    |--------------------------------------------------------------------------
    | Allow Unsafe Links
    |--------------------------------------------------------------------------
    |
    | This option specifies whether to allow risky image URLs and links.
    |
    | Default: true
    |
    */

    'allow_unsafe_links' => true,

    /*
    |--------------------------------------------------------------------------
    | Maximum Nesting Level
    |--------------------------------------------------------------------------
    |
    | This option specifies the maximum permitted block nesting level.
    |
    | Default: INF
    |
    */

    'max_nesting_level' => INF,

];
