<?php
/**
 * Essence Validator
 *
 * @copyright 2016 Cyberitas Technologies, LLC
 * @license LGPL-3.0+
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
     * @inheritdoc
     */
    public $enableClientValidation = false;

    /**
     * @var Essence\Essence Instance of Essence for processing oEmbed media
     */
    private $Essence = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->Essence = new Essence();
        $this->filter = array($this->Essence, 'replace');

        parent::init();
    }
}
