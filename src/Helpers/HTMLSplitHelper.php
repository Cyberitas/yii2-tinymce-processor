<?php
/**
 * HTML splitting helper
 *
 * @copyright 2016 Cyberitas Technologies, LLC
 * @license LGPL-3.0+
 */
namespace Cyberitas\TinymceProcessor\Helpers;

/**
 * Helper for splitting an HTML string. From WordPress's `wp_html_split()` and
 * `get_html_split_regex()`.
 */
final class HTMLSplitHelper
{
    /**
     * @var string regular expression for splitting an HTML string
     */
    protected static $HTML_SPLIT_REGEX;

    /**
     * Prepare the regular expression for splitting an HTML string. From
     * WordPress' `get_html_split_regex()`.
     *
     * @return string regular expression for splitting an HTML string
     * @see https://core.trac.wordpress.org/browser/tags/4.4.2/src/wp-includes/formatting.php#L591
     */
    protected static function getHtmlSplitRegex()
    {
        if (strlen(self::$HTML_SPLIT_REGEX) === 0) {
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

            self::$HTML_SPLIT_REGEX = $regex;
        }

        return self::$HTML_SPLIT_REGEX;
    }

    /**
     * Split an HTML string. From `wp_html_split()`.
     *
     * @param string $html string to split
     * @param int $flags flags to pass to `preg_split()`
     * @return array input string split by HTML tags
     * @see https://core.trac.wordpress.org/browser/tags/4.4.2/src/wp-includes/formatting.php#L579
     */
    public static function splitHtml($html, $flags = PREG_SPLIT_DELIM_CAPTURE)
    {
        return preg_split(self::getHtmlSplitRegex(), $html, -1, $flags);
    }

    /**
     * Replace text within HTML elements only. From WordPress's
     * `wp_replace_in_html_tags()`.
     *
     * @param string $haystack text to be formatted
     * @param array $replacements set of text replacements ("from" => "to")
     * @return string formatted string
     * @see https://core.trac.wordpress.org/browser/tags/4.4.2/src/wp-includes/formatting.php#L715
     */
    public static function replaceInTags($haystack, $replacements)
    {
        $splitHtml = self::splitHtml($haystack);
        $changed = false;
        $needles = array_keys($replacements);

        for ($i = 1, $c = count($splitHtml); $i < $c; $i += 2) {
            foreach ($needles as $needle) {
                if (strpos($splitHtml[$i], $needle) !== false) {
                    $splitHtml[$i] = strtr($spiltHtml[$i], $replacements);
                    $changed = true;
                    break; // look at next element after one strtr()
                }
            }
        }

        if ($changed) {
            $haystack = implode($splitHtml);
        }

        return $haystack;
    }
}
