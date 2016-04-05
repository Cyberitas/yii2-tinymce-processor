<?php
/**
 * Texturize Validator
 *
 * @copyright 2016 Cyberitas Technologies, LLC
 * @license LGPL-3.0+
 */
namespace Cyberitas\TinymceProcessor\Validators;

use yii\validators\FilterValidator;

/**
 * Validator that replicates WordPress's `wptexturize()` to format text for
 * presentation.
 */
class TexturizeValidator extends FilterValidator
{
    /**
     * @const array map of static character replacements
     */
    const STATIC_TRANSLATIONS = [
        '(c)'  => '&#169;',
        '(r)'  => '&#174;',
        '...'  => '&#8230;',
        '(tm)' => '&#8242;',
    ];

    /**
     * @inheritdoc
     */
    public $enableClientValidation = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->filter = function ($value) {
            return $this->texturize($value);
        };

        parent::init();
    }

    /**
     * Performs formatting conversions and replacements on a string.
     *
     * @param string $value string to be formatted
     * @return string formatted string
     */
    protected function texturize($value)
    {
        $value = strtr($value, self::STATIC_TRANSLATIONS);

        return $value;
    }
}
