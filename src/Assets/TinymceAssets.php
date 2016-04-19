<?php
/**
 * TinyMCE Asset Bundle
 *
 * @copyright 2016 Cyberitas Technologies, LLC
 * @license LGPL v3
 */
namespace Cyberitas\TinymceProcessor\Assets;

use yii\web\AssetBundle;

/**
 * Bundle all necessary vendored TinyMCE assets.
 */
class TinymceAssets extends AssetBundle
{
    /**
     * @var boolean debug mode, insert minified JS and CSS if false
     */
    public $debug = false;

    /**
     * @var string TinyMCE skin to include
     */
    public $skin = 'lightgray';

    /**
     * @var string path to vendored TinyMCE assets
     */
    public $sourcePath = '@vendor/tinymce/tinymce';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Add debug or minified CSS for the specified skin to the bundle
        $this->css[] = 'skins/' . $this->skin . '/skin.' . ($this->debug ? '' : 'min.') . 'css';

        // Add debug or minified JS for TinyMCE and Modern theme to the bundle
        $this->js[] = $this->debug ? 'tinymce.js' : 'tinymce.min.js';
        $this->js[] = $this->debug ? 'themes/modern/theme.js' : 'themes/modern/theme.min.js';
    }
}
