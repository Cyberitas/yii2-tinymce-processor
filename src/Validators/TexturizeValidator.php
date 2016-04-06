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
     * @const array default static character replacements
     */
    const DEFAULT_STATIC_TRANSLATIONS = [
        '(c)'  => '&#169;',
        '(r)'  => '&#174;',
        '...'  => '&#8230;',
        '(tm)' => '&#8242;',
    ];

    /**
     * @var string opening single quote character
     */
    public $leftSingleQuote = '&#8216;';

    /**
     * @var string closing single quote/apostrophe character
     */
    public $rightSingleQuote = '&#8217;';

    /**
     * @var string opening double quote character
     */
    public $leftDoubleQuote = '&#8220;';

    /**
     * @var string closing double quote character
     */
    public $rightDoubleQuote = '&#8221;';

    /**
     * @var array set of static character replacements
     */
    protected $staticTranslations = self::DEFAULT_STATIC_TRANSLATIONS;

    /**
     * @var array set of dynamic character replacements
     */
    protected $dynamicTranslations = [];

    /**
     * @var string regular expression to match whitespace, from WordPress'
     * `wp_spaces_regexp()`
     */
    protected $spaces = '[\r\n\t ]|\xC2\xA0|&nbsp;';

    /**
     * @inheritdoc
     */
    public $enableClientValidation = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->filter = array($this, 'texturize');

        parent::init();

        $this->staticTranslations = array_merge($this->staticTranslations, [
            '``'   => $this->leftDoubleQuote,
            '\'\'' => $this->rightDoubleQuote
        ]);

        $this->prepareDynamicTranslations();
    }

    /**
     * Prepare dynamic replacement statements. Extracted from WordPress'
     * `wptexturize()`.
     *
     * @see https://core.trac.wordpress.org/browser/tags/4.4.2/src/wp-includes/formatting.php#L41
     */
    protected function prepareDynamicTranslations()
    {
        // Assume an abbreviated year at the end of a quotation
        // e.g.: '99', '99"
        if ($this->leftSingleQuote !== '\'' || $this->rightSingleQuote !== '\'') {
            $this->dynamicTranslations['/\'(\d\d)\'(?=\Z|[.,:;!?)}\-\]]|&gt;|' . $this->spaces . ')/'] =
                $this->leftSingleQuote . '$1' . $this->rightSingleQuote;
        }

        if ($this->leftSingleQuote !== '\'' || $this->rightDoubleQuote !== '"') {
            $this->dynamicTranslations['/\'(\d\d)"(?=\Z|[.,:;!?)}\-\]]|&gt;|' . $this->spaces . ')/'] =
                $this->leftSingleQuote . '$1' . $this->rightDoubleQuote;
        }

        // '99, '99s, '99's, but not '9, '99%, '999, or '99.0.
        if ($this->leftSingleQuote !== '\'') {
            $this->dynamicTranslations['/\'(?=\d\d(?:\Z|(?![%\d]|[.,]\d)))/'] = $this->leftSingleQuote;
        }

        // Single-quoted numbers
        // e.g. '0.42'
        if ($this->leftSingleQuote !== '\'' && $this->rightSingleQuote !== '\'') {
            $this->dynamicTranslations['/(?<=\A|' . $this->spaces . ')\'(\d[.,\d]*)\'/'] =
                $this->leftSingleQuote . '$1' . $this->rightSingleQuote;
        }

        // Single quote at start or after (, {, <, [, ", -, or whitespace.
        if ($this->leftSingleQuote !== '\'') {
            $this->dynamicTranslations['/(?<=\A|[([{"\-]|&lt;|' . $this->spaces . ')\'/'] = $this->leftSingleQuote;
        }

        // Apostrophe in a word; no spaces, double apostrophes, or other
        // punctuation.
        if ($this->rightSingleQuote !== '\'') {
            $this->dynamicTranslations['/(?<!' . $this->spaces . ')\'(?!\Z|[.,:;!?"\'(){}[\]\-]|&[lg]t;|' .
                $this->spaces . ')/'] = $this->rightSingleQuote;
        }

        // Double-quoted numbers
        // e.g. "42"
        if ($this->leftDoubleQuote !== '"' && $this->rightDoubleQuote !== '"') {
            $this->dynamicTranslations['/(?<=\A|' . $this->spaces . ')"(\d[.,\d]*)"/'] =
                $this->leftDoubleQuote . '$1' . $this->rightDoubleQuote;
        }

        // Double quote at start or after (, {, <, [, -, or whitespace, and not
        // followed by whitespace.
        if ($this->leftDoubleQuote !== '"') {
            $this->dynamicTranslations['/(?<=\A|[([{\-]|&lt;|' . $this->spaces . ')"(?!' . $this->spaces . ')/'] =
                $this->leftDoubleQuote;
        }
    }

    /**
     * Performs formatting conversions and replacements on a string.
     *
     * @param string $value string to be formatted
     * @return string formatted string
     */
    protected function texturize($value)
    {
        $value = strtr($value, $this->staticTranslations);

        foreach ($this->dynamicTranslations as $pattern => $replacement) {
            $value = preg_replace($pattern, $replacement, $value);
        }

        return $value;
    }
}
