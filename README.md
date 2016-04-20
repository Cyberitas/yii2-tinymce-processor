# yii2-tinymce-processor

[![Build Status](https://travis-ci.org/Cyberitas/yii2-tinymce-processor.svg?branch=master)](https://travis-ci.org/Cyberitas/yii2-tinymce-processor)

Yii 2 extension providing WordPress-style text processing from a TinyMCE editor.

## Features

- [Essence][] oEmbed processing
- Texturization, replicating [`wptexturize()`](https://codex.wordpress.org/Function_Reference/wptexturize)
- Auto-paragraphing, replicating [`wpautop()`](https://codex.wordpress.org/Function_Reference/wpautop)
- [HTMLPurifier][] filtering, via Yii's [`HtmlPufirier` helper](http://www.yiiframework.com/doc-2.0/yii-helpers-htmlpurifier.html)
- Yii 2 asset bundle for easy editor insertion

## Usage

```bash
composer require "cyberitas/yii2-tinymce-processor"
```

### Processor

```php
use Cyberitas\TinymceProcessor\TinymceProcessor;

$tmp = new TinymceProcessor();
$tmp->configure([
    'autop' => true,
    'essence' => true,
    'purify' => [
        'purifierConfig' => [
            'Attr.EnableId' => true
        ]
    ],
    'texturize' => [
        'leftDoubleQuote' => '&laquo;',
        'rightDoubleQuote' => '&raquo;'
    ]
]);
$output = $tmp->process("This is some content from a TinyMCE editor.");
```

### Asset Bundle

```php
use Cyberitas\TinymceProcessor\Assets\TinymceAssets;
use yii\helpers\Html;
use yii\widgets\InputWidget;

class TinymceWidget extends InputWidget
{
    public function run()
    {
        TinymceAssets::register($view);
        $this->view->registerJs('tinymce.init({selector: "textarea"});');
        echo Html::textarea($this->name, $this->value);
    }
}
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
[HTMLPurifier]: http://htmlpurifier.org/
[Cyberitas Technologies, LLC]: http://www.cyberitas.com/
