<?php
/**
 * Auto-Paragraph Validator
 *
 * @copyright 2016 Cyberitas Technologies, LLC
 * @license LGPL-3.0+
 */
namespace Cyberitas\TinymceProcessor\Validators;

use Cyberitas\TinymceProcessor\Helpers\HTMLSplitHelper;
use yii\validators\FilterValidator;

/**
 * Validator that replicates WordPress' `wpautop()` to convert consecutive line
 * breaks into HTML paragraphs.
 */
class AutoParagraphValidator extends FilterValidator
{
    /**
     * @var array list of HTML block elements not to wrap in paragraphs
     */
    private static $BLOCK_ELEMENTS = [ 'table', 'thead', 'tfoot', 'caption', 'col', 'colgroup', 'tbody', 'tr', 'td',
        'th', 'div', 'dl', 'dd', 'dt', 'ul', 'ol', 'li', 'pre', 'form', 'map', 'area', 'blockquote', 'address', 'math',
        'style', 'p', 'h[1-6]', 'hr', 'fieldset', 'legend', 'section', 'article', 'aside', 'hgroup', 'header', 'footer',
        'nav', 'figure', 'figcaption', 'details', 'menu', 'summary', 'iframe' ];

    /**
     * @var string placeholder flag for newlines
     */
    private static $NEWLINE_PLACEHOLDER_FLAG = '<!-- cynewline -->';

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
        $preTags = [];

        if (trim($value) === '') {
            return '';
        }

        $value .= "\n";

        // Skip <pre> tags by replacing them with placeholders and reinserting
        // them after processing.
        if (strpos($value, '<pre') !== false) {
            $parts = explode('</pre>', $value);
            $last = array_pop($parts);
            $value = '';
            $i = 0;

            foreach ($parts as $part) {
                $start = strpos($part, '<pre');

                if ($start === false) { // malformed HTML
                    $value .= $part;
                    continue;
                }

                $name = "<pre pre-tag-$i></pre>";
                $preTags[$name] = substr($part, $start) . '</pre>';

                $value .= substr($part, 0, $start) . $name;
                $i++;
            }

            $value .= $last;
        }

        // Replace consecutive <br>s with a double line break for paragraphing
        $value = preg_replace('|<br\s*/?>\s*<br\s*/?>|', "\n\n", $value);

        // Single line break before, double line break after all block elements
        $allBlocks = '(?:' . implode('|', self::$BLOCK_ELEMENTS) . ')';
        $value = preg_replace('!(<' . $allBlocks . '[\s/>])!', "\n$1", $value);
        $value = preg_replace('!(</' . $allBlocks . '>)!', "$1\n\n", $value);

        // Placehold newlines within tags
        $value = HTMLSplitHelper::replaceInTags($value, array("\n" => self::$NEWLINE_PLACEHOLDER_FLAG));

        if (strpos($value, '<option') !== false) { // collapse line breaks around <option>s
            $value = preg_replace('|\s*<option|', '<option', $value);
            $value = preg_replace('|</option>\s*|', '</option>', $value);
        }

        if (strpos($value, '</object>') !== false) { // collapse line breaks within <object>s
            $value = preg_replace('|(<object[^>]*>)\s*|', '$1', $value);
            $value = preg_replace('|\s*</object>|', '</object>', $value);
            $value = preg_replace('%\s*(</?(?:param|embed)[^>]*>)\s*%', '$1', $value);
        }

        if (strpos($value, '<source') !== false || strpos($value, '<track') !== false) {
            // collapse line breaks within <audio>s and <video>s
            $value = preg_replace('%([<\[](?:audio|video)[^>\]]*[>\]])\s*%', '$1', $value);
            $value = preg_replace('%\s*([<\[]/(?:audio|video)[>\]])%', '$1', $value);
            $value = preg_replace('%\s*(<(?:source|track)[^>]*>)\s*%', '$1', $value);
        }

        // Remove more than two consecutive line breaks
        $value = preg_replace("/\n\n+/", "\n\n", $value);

        $valueSplit = preg_split("/\n\s*\n/", $value, -1, PREG_SPLIT_NO_EMPTY);
        $value = '';

        foreach ($valueSplit as $p) {
            $value .= '<p>' . trim($p, "\n") . "</p>\n";
        }

        // Remove paragraphs that contain only whitespace
        $value = preg_replace('|<p>\s*</p>|', '', $value);

        // Add missing closing <p>s inside <div>, <address>, or <form>
        $value = preg_replace('!<p>([^<]+)</(div|address|form)>!', "<p>$1</p></$2>", $value);

        // Remove <p>s wrapped around block element tags
        $value = preg_replace('!<p>\s*(</?' . $allBlocks . '[^>]*>)\s*</p>!', '$1', $value);

        // Remove <p>s wrapped around <li>s
        $value = preg_replace('|<p>(<li.+?)</p>|', '$1', $value);

        // Move <p>s inside <blockquote>s
        $value = preg_replace('|<p><blockquote([^>]*)>|i', '<blockquote$1><p>', $value);
        $value = str_replace('</blockquote></p>', '</p></blockquote>', $value);

        // Remove <p>s before or after opening or closing block element tags
        $value = preg_replace('!<p>\s*(</?' . $allBlocks . '[^>]*>)!', '$1', $value);
        $value = preg_replace('!(</?' . $allBlocks . '[^>]*>)\s*</p>!', '$1', $value);

        if ($this->convertBr) {
            // Preserve newlines in <script> and <style>
            $value = preg_replace_callback('/<(script|style).*?<\/\\1>/s', function ($matches) {
                return str_replace("\n", "<!-- preserveNewline -->", $matches[0]);
            }, $value);

            // Normalize <br />s
            $value = str_replace(array('<br>', '<br/>'), '<br />', $value);

            // Replace newlines that don't follow a <br /> with <br />
            $value = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $value);

            // Restore preserved newlines
            $value = str_replace('<!-- preserveNewline -->', "\n", $value);
        }

        // Remove <br />s after opening or closing block tags
        $value = preg_replace('!(</?' . $allBlocks . '[^>]*>)\s*<br />!', '$1', $value);

        // Remove <br />s before certain opening or closing tags
        $value = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $value);
        $value = preg_replace('|\n</p>$|', '</p>', $value);

        if (!empty($preTags)) { // Restore <pre>s
            $value = str_replace(array_keys($preTags), array_values($preTags), $value);
        }

        // Restore newlines
        if (strpos($value, self::$NEWLINE_PLACEHOLDER_FLAG) !== false) {
            $value = str_replace(self::$NEWLINE_PLACEHOLDER_FLAG, "\n", $value);
        }

        return trim($value);
    }
}
