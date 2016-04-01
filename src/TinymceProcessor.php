<?php
/**
 * Yii 2 TinyMCE Processor
 *
 * Yii 2 extension providing WordPress-style text processing from a TinyMCE
 * editor.
 *
 * @copyright 2016 Cyberitas Technologies, LLC
 * @license LGPL-3.0+
 */
namespace Cyberitas\TinymceProcessor;

use yii\base\Model;

/**
 * Model that applies all text processing to an input value from a TinyMCE
 * editor.
 */
class TinymceProcessor extends Model
{
    /**
     * @var string Content from a TinyMCE editor
     */
    public $content;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['content', 'string']
        ];
    }
}
