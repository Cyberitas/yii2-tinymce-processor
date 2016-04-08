<?php
/**
 * Auto-Paragraph Validator
 *
 * @copyright 2016 Cyberitas Technologies, LLC
 * @license LGPL-3.0+
 */
namespace Cyberitas\TinymceProcessor\Validators;

use yii\validators\FilterValidator;

/**
 * Validator that replicates WordPress' `wpautop()` to convert consecutive line
 * breaks into HTML paragraphs.
 */
class AutoParagraphValidator extends FilterValidator
{
    /**
     * @var bool convert additional line breaks to HTML line breaks
     */
    public $convertBr = true;

    /**
     * @inheritdoc
     */
    public $enableClientValidation = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->filter = array($this, 'autop');

        parent::init();
    }

    /**
     * Convert consecutive line breaks into HTML paragraphs, and optionally
     * (enabled by default) converts additional line breaks into HTML line
     * breaks.
     *
     * @param string $value string to be formatted
     * @return string formatted string
     * @see https://core.trac.wordpress.org/browser/tags/4.4.2/src/wp-includes/formatting.php#L415
     */
    protected function autop($value)
    {
        return $value;
    }
}
