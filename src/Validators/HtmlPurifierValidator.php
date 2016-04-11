<?php
/**
 * HTMLPurifier Validator
 *
 * @copyright 2016 Cyberitas Technologies, LLC
 * @license LGPL-3.0+
 */
namespace Cyberitas\TinymceProcessor\Validators;

use yii\helpers\HtmlPurifier;
use yii\validators\FilterValidator;

/**
 * Validator that purifies entered HTML.
 */
class HtmlPurifierValidator extends FilterValidator
{
    /**
     * @var array HTMLPurifier configuration
     * @see http://htmlpurifier.org/live/configdoc/plain.html
     */
    public $purifierConfig = [];

    /**
     * @inheritdoc
     */
    public $enableClientValidation = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->filter = array($this, 'purify');

        parent::init();
    }

    /**
     * Purify the provided HTML string with HTMLPurifier.
     *
     * @param string $value HTML to purify
     * @return string purified HTML
     * @see http://www.yiiframework.com/doc-2.0/yii-helpers-basehtmlpurifier.html#process()-detail
     */
    protected function purify($value)
    {
        return HtmlPurifier::process($value, $this->purifierConfig);
    }
}
