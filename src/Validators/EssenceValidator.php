<?php
/**
 * Essence Validator
 *
 * @copyright 2016 Cyberitas Technologies, LLC
 * @license LGPL v3
 */
namespace Cyberitas\TinymceProcessor\Validators;

use Essence\Essence;
use yii\validators\FilterValidator;

/**
 * Validator that uses Essence to replace oEmbed URLs with the embedded media.
 */
class EssenceValidator extends FilterValidator
{
    /**
     * @var array Essence provider configuration
     * @see https://github.com/essence/essence/tree/3.1.1#configuration
     */
    public $essenceConfig = [];

    /**
     * @var callable URL replacement callback
     * @see https://github.com/essence/essence/tree/3.1.1#replacing-urls-in-text
     */
    public $replaceTemplate = null;

    /**
     * @var array options to pass to the oEmbed providers
     * @see https://github.com/essence/essence/tree/3.1.1#configuring-providers
     */
    public $replaceOptions = [];

    /**
     * @inheritdoc
     */
    public $enableClientValidation = false;

    /**
     * @var Essence\Essence Instance of Essence for processing oEmbed media
     */
    protected $Essence = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->filter = array($this, 'replace');

        parent::init();

        $this->Essence = new Essence($this->essenceConfig);
    }

    /**
     * Replace URLs with oEmbed media with Essence.
     *
     * @param string $value HTML to process
     * @return string processed HTML
     * @see https://github.com/essence/essence/blob/3.1.1/lib/Essence/Replacer.php#L67
     */
    protected function replace($value)
    {
        return $this->Essence->replace($value, $this->replaceTemplate, $this->replaceOptions);
    }
}
