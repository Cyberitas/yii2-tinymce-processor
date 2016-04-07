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
    private static $DEFAULT_STATIC_TRANSLATIONS = [
        '(c)'  => '&#169;',
        '(r)'  => '&#174;',
        '...'  => '&#8230;',
        '(tm)' => '&#8242;',
    ];

    /**
     * @const array default tags whose contents should not be texturized
     */
    private static $DEFAULT_NO_TEXTURIZE_TAGS = [
        'pre',
        'code',
        'kbd',
        'style',
        'script',
        'tt'
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
     * @var string em dash
     */
    public $emDash = '&#8211;';

    /**
     * @var string en dash
     */
    public $enDash = '&#8212;';

    /**
     * @var array tags whose contents should not be texturized
     */
    public $noTexturizeTags;

    /**
     * @var array set of static character replacements
     */
    protected $staticTranslations;

    /**
     * @var array set of dynamic character replacements
     */
    protected $dynamicTranslations = [
        'singleQuotes' => [],
        'doubleQuotes' => [],
        'dashes'       => []
    ];

    /**
     * @var string regular expression to match whitespace, from WordPress'
     * `wp_spaces_regexp()`
     */
    protected $spaces = '[\r\n\t ]|\xC2\xA0|&nbsp;';

    /**
     * @var string regular expression for splitting an HTML string
     */
    protected $htmlSplitRegex;

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

        $this->staticTranslations = array_merge(self::$DEFAULT_STATIC_TRANSLATIONS, [
            '``'   => $this->leftDoubleQuote,
            '\'\'' => $this->rightDoubleQuote
        ]);

        if (!is_array($this->noTexturizeTags)) {
            $this->noTexturizeTags = self::$DEFAULT_NO_TEXTURIZE_TAGS;
        }

        $this->prepareDynamicTranslations();
        $this->prepareHtmlSplitRegex();
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
            $this->dynamicTranslations['singleQuotes']['/\'(\d\d)\'(?=\Z|[.,:;!?)}\-\]]|&gt;|' . $this->spaces . ')/'] =
                $this->leftSingleQuote . '$1' . $this->rightSingleQuote;
        }

        if ($this->leftSingleQuote !== '\'' || $this->rightDoubleQuote !== '"') {
            $this->dynamicTranslations['singleQuotes']['/\'(\d\d)"(?=\Z|[.,:;!?)}\-\]]|&gt;|' . $this->spaces . ')/'] =
                $this->leftSingleQuote . '$1' . $this->rightDoubleQuote;
        }

        // '99, '99s, '99's, but not '9, '99%, '999, or '99.0.
        if ($this->leftSingleQuote !== '\'') {
            $this->dynamicTranslations['singleQuotes']['/\'(?=\d\d(?:\Z|(?![%\d]|[.,]\d)))/'] = $this->leftSingleQuote;
        }

        // Single-quoted numbers
        // e.g. '0.42'
        if ($this->leftSingleQuote !== '\'' && $this->rightSingleQuote !== '\'') {
            $this->dynamicTranslations['singleQuotes']['/(?<=\A|' . $this->spaces . ')\'(\d[.,\d]*)\'/'] =
                $this->leftSingleQuote . '$1' . $this->rightSingleQuote;
        }

        // Single quote at start or after (, {, <, [, ", -, or whitespace.
        if ($this->leftSingleQuote !== '\'') {
            $this->dynamicTranslations['singleQuotes']['/(?<=\A|[([{"\-]|&lt;|' . $this->spaces . ')\'/'] =
                $this->leftSingleQuote;
        }

        // Apostrophe in a word; no spaces, double apostrophes, or other
        // punctuation.
        if ($this->rightSingleQuote !== '\'') {
            $this->dynamicTranslations['singleQuotes']['/(?<!' . $this->spaces .
                ')\'(?!\Z|[.,:;!?"\'(){}[\]\-]|&[lg]t;|'. $this->spaces . ')/'] = $this->rightSingleQuote;
        }

        // Double-quoted numbers
        // e.g. "42"
        if ($this->leftDoubleQuote !== '"' && $this->rightDoubleQuote !== '"') {
            $this->dynamicTranslations['doubleQuotes']['/(?<=\A|' . $this->spaces . ')"(\d[.,\d]*)"/'] =
                $this->leftDoubleQuote . '$1' . $this->rightDoubleQuote;
        }

        // Double quote at start or after (, {, <, [, -, or whitespace, and not
        // followed by whitespace.
        if ($this->leftDoubleQuote !== '"') {
            $this->dynamicTranslations['doubleQuotes']['/(?<=\A|[([{\-]|&lt;|' . $this->spaces . ')"(?!' .
                $this->spaces . ')/'] = $this->leftDoubleQuote;
        }

        // Dashes and spaces
        $this->dynamicTranslations['dashes']['/---/'] = $this->emDash;
        $this->dynamicTranslations['dashes']['/(?<=^|' . $this->spaces . ')--(?=$|' . $this->spaces .
            ')/'] = $this->emDash;
        $this->dynamicTranslations['dashes']['/(?<!xn)--/'] = $this->enDash;
        $this->dynamicTranslations['dashes']['/(?<=^|' . $this->spaces . ')-(?=$|' . $this->spaces .
            ')/'] = $this->enDash;
    }

    /**
     * Prepare the regular expression for splitting an HTML string. From
     * WordPress' `get_html_split_regex()`.
     *
     * @see https://core.trac.wordpress.org/browser/tags/4.4.2/src/wp-includes/formatting.php#L591
     */
    protected function prepareHtmlSplitRegex()
    {
        $comments =
            '!'           // Start of comment, after the <.
            . '(?:'         // Unroll the loop: Consume everything until --> is found.
            .     '-(?!->)' // Dash not followed by end of comment.
            .     '[^\-]*+' // Consume non-dashes.
            . ')*+'         // Loop possessively.
            . '(?:-->)?';   // End of comment. If not found, match all input.

        $cdata =
            '!\[CDATA\['  // Start of comment, after the <.
            . '[^\]]*+'     // Consume non-].
            . '(?:'         // Unroll the loop: Consume everything until ]]> is found.
            .     '](?!]>)' // One ] not followed by end of comment.
            .     '[^\]]*+' // Consume non-].
            . ')*+'         // Loop possessively.
            . '(?:]]>)?';   // End of comment. If not found, match all input.

        $escaped =
            '(?='           // Is the element escaped?
            .    '!--'
            . '|'
            .    '!\[CDATA\['
            . ')'
            . '(?(?=!-)'      // If yes, which type?
            .     $comments
            . '|'
            .     $cdata
            . ')';

        $regex =
            '/('              // Capture the entire match.
            .     '<'           // Find start of element.
            .     '(?'          // Conditional expression follows.
            .         $escaped  // Find end of escaped element.
            .     '|'           // ... else ...
            .         '[^>]*>?' // Find end of normal element.
            .     ')'
            . ')/';

        $this->htmlSplitRegex = $regex;
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

        // TODO: break this out into separate preg_replace calls for
        // singleQuotes, doubleQuotes, and dashes
        foreach (array_values($this->dynamicTranslations) as $translations) {
            $value = preg_replace(array_keys($translations), array_values($translations), $value);
        }

        return $value;
    }

    /**
     * Search for disabled element tags, push to stack on open and pop on close.
     * From WordPress' `_wptexturize_pushpop_element()`.
     *
     * @param string $element HTML tag to check and push or pop
     * @param array $stack list of open tag elements
     * @see https://core.trac.wordpress.org/browser/tags/4.4.2/src/wp-includes/formatting.php#L361
     */
    protected function pushPopElement($element, $stack)
    {
        if ($element[1] !== '/') { // opening tag
            $openingTag = true;
            $offset = 1;
        } elseif (count($stack) === 0) { // empty stack
            return;
        } else { // closing tag
            $openingTag = false;
            $offset = 2;
        }

        $space = strpos($element, ' ');
        if ($space === false) {
            $space = -1;
        } else {
            $space -= $offset;
        }

        $tagName = substr($element, $offset, $space);

        if (in_array($tagName, $this->noTexturizeTags)) {
            if ($openingTag) {
                array_push($stack, $tagName);
            } elseif (end($stack) === $tagName) {
                array_pop($stack);
            }
        }
    }
}
