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
     * @const array default processor configuration
     */
    const DEFAULT_CONFIG = [
        'essence'   => true,
        'texturize' => true
    ];

    /**
     * @var string content from a TinyMCE editor to be processed
     */
    public $content;

    /**
     * @var array processor and validator configuration
     */
    protected $config = self::DEFAULT_CONFIG;

    /**
     * @var array validation rules and configuration
     */
    protected $rules = [
        ['content', 'string']
    ];

    /**
     * Conifgure the processor and validators.
     *
     * @param array $config configuration array. Should be an associative array
     * with keys being the validators (e.g. `"essence"`) and values of:
     *
     * - `true`: enable the validator with its default configuration
     * - array: enable the validator and override the default configuration
     * - `false`: disable the validator
     */
    public function configure($config)
    {
        $this->config = array_replace($this->config, $config);
    }

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
    public function beforeValidate()
    {
        $this->configureRules();

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return $this->rules;
    }

    /**
     * Configure validation rules from the processor configuration.
     */
    private function configureRules()
    {
        foreach ($this->config as $key => $value) {
            // If the validator is not disabled...
            if (false !== $value) {
                $validator = ['content'];

                // ...and it exists...
                switch ($key) {
                    // ...add it to the list of rules.
                    case 'essence':
                        array_push($validator, EssenceValidator::className());
                        break;
                    case 'texturize':
                        array_push($validator, TexturizeValidator::className());
                        break;
                    // If it doesn't exist, don't add it.
                    default:
                        continue 2;
                }

                // If configuration is provided for the validator, include it in
                // the rule.
                if (is_array($value)) {
                    array_push($validator, $value);
                }

                array_push($this->rules, $validator);
            }
        }
    }
}
