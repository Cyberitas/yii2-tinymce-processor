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
use Cyberitas\TinymceProcessor\Validators\EssenceValidator;
use Cyberitas\TinymceProcessor\Validators\TexturizeValidator;

/**
 * Model that applies all text processing to an input value from a TinyMCE
 * editor.
 */
class TinymceProcessor extends Model
{
    /**
     * @var string Content from a TinyMCE editor to be processed
     */
    public $content;

    /**
     * Process a string with the configured validators.
     *
     * @param string $content string from a TinyMCE editor to be processed
     * @return string processed content
     */
    public function process($content)
    {
        $this->content = $content;
        $this->validate();
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['content', 'string'],
            ['content', EssenceValidator::className()],
            ['content', TexturizeValidator::className()]
        ];
    }
}
