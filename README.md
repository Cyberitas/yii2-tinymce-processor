# yii2-tinymce-processor

Yii 2 extension providing WordPress-style text processing from a TinyMCE editor.

## Features

- [Essence][] oEmbed processing
- Texturization, replicating [`wptexturize()`](https://codex.wordpress.org/Function_Reference/wptexturize)

## Usage

```bash
composer require "cyberitas/yii2-tinymce-processor"
```

```php
$tmp = new TinymceProcessor();
$tmp->configure([
    'essence' => true,
    'texturize' => [
        'leftDoubleQuote' => '&laquo;',
        'rightDoubleQuote' => '&raquo;'
    ]
]);
$output = $tmp->process("This is some content from a TinyMCE editor.");
```

## Copyright

Copyright © 2016 [Cyberitas Technologies, LLC][]. This program is free software:
you can redistribute it and/or modify it under the terms of the GNU Lesser
General Public License as published by the Free Software Foundation, either
version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.

[Essence]: http://essence.github.io/essence/
[Cyberitas Technologies, LLC]: http://www.cyberitas.com/
