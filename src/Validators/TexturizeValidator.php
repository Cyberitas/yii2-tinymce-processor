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
     * @inheritdoc
     */
    public $enableClientValidation = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }
}
