<?php
/**
 * Texturize Validator
 *
 * @copyright 2016 Cyberitas Technologies, LLC
 * @license LGPL v3
 */
namespace Cyberitas\TinymceProcessor\Validators;

use Cyberitas\TinymceProcessor\Helpers\HTMLSplitHelper;
use yii\validators\FilterValidator;

/**
 * Validator that replicates WordPress's `wptexturize()` to format text for
 * presentation.
 */
class TexturizeValidator extends FilterValidator
{
    /**
     * @const ampersand entity replacement pattern
     */
    const AMPERSAND_ENTITY_PATTERN = '/&(?!#(?:\d+|x[a-f0-9]+);|[a-z1-4]{1,8};)/i';

    /**
     * @const flag for prime or quote replacement
     */
    const PRIME_OR_QUOTE_FLAG = '<!--prime-or-quote-->';

    /**
     * @var array default static character replacements
     */
    protected static $DEFAULT_STATIC_TRANSLATIONS = [
        '(c)'  => '&#169;',
        '(r)'  => '&#174;',
        '...'  => '&#8230;',
        '(tm)' => '&#8242;',
    ];

    /**
     * @var array default tags whose contents should not be texturized
     */
    protected static $DEFAULT_NO_TEXTURIZE_TAGS = [
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
     * @var string prime character
     */
    public $prime = '&#8242;';

    /**
     * @var string double prime character
     */
    public $doublePrime = '&#8243;';

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
     * @inheritdoc
     */
    public $enableClientValidation = false;

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
     * @inheritdoc
     */
    public function init()
    {
        $this->filter = array($this, 'texturize');

        $this->staticTranslations = array_merge(self::$DEFAULT_STATIC_TRANSLATIONS, [
            '``'   => $this->leftDoubleQuote,
            '\'\'' => $this->rightDoubleQuote
        ]);

        if (!is_array($this->noTexturizeTags)) {
            $this->noTexturizeTags = self::$DEFAULT_NO_TEXTURIZE_TAGS;
        }

        $this->prepareDynamicTranslations();

        return parent::init();
    }

    /**
     * Performs formatting conversions and replacements on a string. Extracted
     * from WordPress' `wptexturize()`.
     *
     * @param string $value string to be formatted
     * @return string formatted string
     * @see https://core.trac.wordpress.org/browser/tags/4.4.2/src/wp-includes/formatting.php#L41
     */
    protected function texturize($value)
    {
        $noTexturizeTagStack = [];

        preg_match_all('@\[/?([^<>&/\[\]\x00-\x20=]++)@', $value, $tagNames);
        $tagNames = $tagNames[1];
        $valueSplit = HTMLSplitHelper::splitHtml($value, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($valueSplit as &$chunk) {
            if ($chunk[0] === '<') {
                if (substr($chunk, 0, 4 === '<!--')) { // HTML comment
                    continue;
                } else { // HTML element
                    $chunk = preg_replace(self::AMPERSAND_ENTITY_PATTERN, '&#038;', $chunk);
                    $this->pushPopElement($chunk, $noTexturizeTagStack);
                }
            } elseif (trim($chunk) === '') { // newline between delimiters
                continue;
            } elseif (empty($noTexturizeTagStack)) {
                $chunk = strtr($chunk, $this->staticTranslations);

                if (strpos($chunk, "'") !== false) {
                    $chunk = preg_replace(
                        array_keys($this->dynamicTranslations['singleQuotes']),
                        array_values($this->dynamicTranslations['singleQuotes']),
                        $chunk
                    );

                    $chunk = $this->texturizePrimes(
                        $chunk,
                        "'",
                        $this->prime,
                        $this->leftSingleQuote,
                        $this->rightSingleQuote
                    );
                }

                if (strpos($chunk, '"') !== false) {
                    $chunk = preg_replace(
                        array_keys($this->dynamicTranslations['doubleQuotes']),
                        array_values($this->dynamicTranslations['doubleQuotes']),
                        $chunk
                    );

                    $chunk = $this->texturizePrimes(
                        $chunk,
                        '"',
                        $this->doublePrime,
                        $this->leftDoubleQuote,
                        $this->rightDoubleQuote
                    );
                }

                if (strpos($chunk, '-') !== false) {
                    $chunk = preg_replace(
                        array_keys($this->dynamicTranslations['dashes']),
                        array_values($this->dynamicTranslations['dashes']),
                        $chunk
                    );
                }

                if (preg_match('/(?<=\d)x\d/', $chunk) === 1) {
                    $chunk = preg_replace('/\b(\d(?(?<=0)[\d\.,]+|[\d\.,]*))x(\d[\d\.,]*)\b/', '$1&#215;$2', $chunk);
                }

                $chunk = preg_replace(self::AMPERSAND_ENTITY_PATTERN, '&#038;', $chunk);
            }
        }

        return implode('', $valueSplit);
    }

    /**
     * Search for disabled element tags, push to stack on open and pop on close.
     * From WordPress' `_wptexturize_pushpop_element()`.
     *
     * @param string $element HTML tag to check and push or pop
     * @param array $stack list of open tag elements
     * @see https://core.trac.wordpress.org/browser/tags/4.4.2/src/wp-includes/formatting.php#L361
     */
    private function pushPopElement($element, $stack)
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

    /**
     * Determine whether to replace ' or " with a curly quote or prime. From
     * WordPress' `wptexturize_primes()`.
     *
     * @param string $haystack text to be searched
     * @param string $needle character to search for and replace
     * @param string $prime character to use as replacement
     * @param string $openQuote opening quote character already put in place
     * @param string $closeQuote closing quote character to use
     * @return string formatted $haystack text
     * @see https://core.trac.wordpress.org/browser/tags/4.4.2/src/wp-includes/formatting.php#L292
     */
    private function texturizePrimes($haystack, $needle, $prime, $openQuote, $closeQuote)
    {
        $quotePattern = "/$needle(?=\\Z|[.,:;!?)}\\-\\]]|&gt;|" . $this->spaces . ")/";
        $primePattern = "/(?<=\\d)$needle/";
        $flagAfterDigit = "/(?<=\\d)" . self::PRIME_OR_QUOTE_FLAG . "/";
        $flagNoDigit = "/(?<!\\d)" . self::PRIME_OR_QUOTE_FLAG . "/";

        $sentences = explode($openQuote, $haystack);

        foreach ($sentences as $key => &$sentence) {
            if (strpos($sentence, $needle) === false) {
                continue;
            } elseif ($key !== 0 && substr_count($sentence, $closeQuote) === 0) {
                $sentence = preg_replace($quotePattern, self::PRIME_OR_QUOTE_FLAG, $sentence, -1, $count);

                if ($count > 1) { // multiple closing quotes
                    $sentence = preg_replace($flagNoDigit, $closeQuote, $sentence, -1, $count2);

                    if ($count2 === 0) { // quote followed by period?
                        $count2 = substr_count($sentence, self::PRIME_OR_QUOTE_FLAG . ".");

                        if ($count2 > 0) { // rightmost ". is the end of the quotation
                            $pos = strrpos($sentence, self::PRIME_OR_QUOTE_FLAG . ".");
                        } else { // make rightmost candidate a closing quote
                            $pos = strrpos($sentence, self::PRIME_OR_QUOTE_FLAG);
                        }

                        $sentence = substr_replace($sentence, $closeQuote, $pos, strlen(self::PRIME_OR_QUOTE_FLAG));
                    }

                    $sentence = preg_replace($primePattern, $prime, $sentence);
                    $sentence = preg_replace($flagAfterDigit, $prime, $sentence);
                    $sentence = preg_replace(self::PRIME_OR_QUOTE_FLAG, $closeQuote, $sentence);
                } elseif ($count === 1) { // one closing quote found, replace it before primes
                    $sentence = str_replace(self::PRIME_OR_QUOTE_FLAG, $closeQuote, $sentence);
                    $sentence = preg_replace($primePattern, $prime, $sentence);
                } else { // no closing quotes found, just primes
                    $sentence = preg_replace($primePattern, $prime, $sentence);
                }
            } else {
                $sentence = preg_replace($primePattern, $prime, $sentence);
                $sentence = preg_replace($quotePattern, $closeQuote, $sentence);
            }

            if ($needle === '"' && strpos($sentence, '"' !== false)) {
                $sentence = str_replace('"', $closeQuote, $sentence);
            }
        }

        return implode($openQuote, $sentences);
    }

    /**
     * Prepare dynamic replacement statements. Extracted from WordPress'
     * `wptexturize()`.
     *
     * @see https://core.trac.wordpress.org/browser/tags/4.4.2/src/wp-includes/formatting.php#L41
     */
    private function prepareDynamicTranslations()
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
}
