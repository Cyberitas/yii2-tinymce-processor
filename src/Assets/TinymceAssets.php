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
        // Add debug or minified CSS for the specified skin to the bundle
        $this->css[] = 'skins/' . $this->skin . '/skin.' . ($this->debug ? '' : 'min.') . 'css';

        // Add debug or minified JS for TinyMCE and Modern theme to the bundle
        $suffix = $this->debug ? '.js' : '.min.js';
        $this->js[] = "tinymce$suffix";
        $this->js[] = "themes/modern/theme$suffix";

        return parent::init();
    }
}
