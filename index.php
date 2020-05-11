<?php

require __DIR__ . '/bootstrap/simple-errors.php';

displaySimpleError('ROOT', 403, '<span class="important">This is the root directory and it MUST NOT BE PUBLICALLY ACCESSIBLE.</span><br>
    To access Lychee, go <a href="public/">here</a>.');
