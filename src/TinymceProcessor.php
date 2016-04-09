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
use Cyberitas\TinymceProcessor\Validators\AutoParagraphValidator;
use Cyberitas\TinymceProcessor\Validators\EssenceValidator;
use Cyberitas\TinymceProcessor\Validators\HtmlPurifierValidator;
use Cyberitas\TinymceProcessor\Validators\TexturizeValidator;

/**
 * Model that applies all text processing to an input value from a TinyMCE
 * editor.
 */
class TinymceProcessor extends Model
{
    /**
     * @const array validators in the order they should be run
     */
    private static $VALIDATORS = [
        'purify',
        'essence',
        'texturize',
        'autop'
    ];

    /**
     * @const array default processor configuration
     */
    private static $DEFAULT_CONFIG = [
        'autop'     => true,
        'essence'   => true,
        'purify'    => true,
        'texturize' => true
    ];

    /**
     * @var string content from a TinyMCE editor to be processed
     */
    public $content;

    /**
     * @var array processor and validator configuration
     */
    protected $config;

    /**
     * @var array validation rules and configuration
     */
    protected $rules = [
        ['content', 'string']
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->config = self::$DEFAULT_CONFIG;
    }

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
        foreach (self::$VALIDATORS as $validator) {
            // If the validator is not disabled...
            if (array_key_exists($validator, $this->config) && false !== $this->config[$validator]) {
                $rule = ['content'];

                // ...add it to the list of rules.
                switch ($validator) {
                    case 'autop':
                        array_push($rule, AutoParagraphValidator::className());
                        break;
                    case 'essence':
                        array_push($rule, EssenceValidator::className());
                        break;
                    case 'purify':
                        array_push($rule, HtmlPurifierValidator::className());
                        break;
                    case 'texturize':
                        array_push($rule, TexturizeValidator::className());
                        break;
                    // If it doesn't exist, don't add it.
                    default:
                        continue 2;
                }

                // If configuration is provided for the validator, include it in
                // the rule.
                if (is_array($this->config[$validator])) {
                    array_push($v, $this->config[$validator]);
                }

                array_push($this->rules, $rule);
            }
        }
    }
}
