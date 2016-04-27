<?php
/**
 * Auto Paragraph Validator Test
 */
namespace Cyberitas\TinymceProcessor\Tests\unit\Validators;

use Cyberitas\TinymceProcessor\Tests\TestCase;
use Cyberitas\TinymceProcessor\Tests\_support\models\TestModel;
use Cyberitas\TinymceProcessor\Validators\AutoParagraphValidator;

/**
 * Test the auto paragraph validator.
 */
class AutoParagraphValidatorTest extends TestCase
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var TestModel
     */
    protected $m;

    /**
     * @var AutoParagraphValidator
     */
    protected $val;

    public function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    protected function _before()
    {
        $this->m = new TestModel();
        $this->val = new AutoParagraphValidator();
    }

    /**
     * AutoParagraphValidator wraps lines separated by a blank newline in
     * paragraph tags.
     */
    public function testValidateAttribute()
    {
        $this->m->content = <<<EOF
This is some content that should be processed.

This is a new paragraph.
EOF;

        $expected = <<<EOF
<p>This is some content that should be processed.</p>
<p>This is a new paragraph.</p>
EOF;

        $this->val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }

    /**
     * AutoParagraphValidator returns an empty string if the entire content
     * string consists of whitespace.
     */
    public function testAllWhitespace()
    {
        $this->m->content = "\n\t    ";
        $expected = "";

        $this->val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }

    /**
     * AutoParagraphValidator does not process the contents of <pre> tags
     */
    public function testSkipPreContents()
    {
        $this->m->content = <<<EOF
<pre>
This is some

preformatted content.
</pre>

This should be paragraphed.
EOF;

        $expected = <<<EOF
<pre>
This is some

preformatted content.
</pre>
<p>This should be paragraphed.</p>
EOF;

        $this->val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }

    /**
     * AutoParagraphValidator handles mismatched opening and closing <pre> tags
     */
    public function testMalformedPre()
    {
        $this->m->content = <<<EOF
<pre></pre>
This is some malformed HTML.
</pre>
EOF;

        $expected = <<<EOF
<pre></pre>
<p>This is some malformed HTML.</p>
EOF;

        $this->val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }

    /**
     * AutoParagraphValidator collapses whitespace around <option>s
     */
    public function testCollapseWhitespaceAroundOptions()
    {
        $this->m->content = <<<EOF
<select>
    <option>A</option>
    <option>B</option>
    <option>C</option>
</select>
EOF;

        $expected = <<<EOF
<p><select><option>A</option><option>B</option><option>C</option></select></p>
EOF;

        $this->val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }

    /**
     * AutoParagraphValidator collapses whitespace within <object>s
     */
    public function testCollapseWhitespaceInObjects()
    {
        $this->m->content = <<<EOF
<object width="100%" height="100%" data="/test.swf">
    <param name="test" value="true">
</object>
EOF;

        $expected = <<<EOF
<p><object width="100%" height="100%" data="/test.swf"><param name="test" value="true"></object></p>
EOF;

        $this->val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }

    /**
     * AutoParagraphValidator collapses whitespace within <audio>s and <video>s
     */
    public function testCollapseWhitespaceInAudioVideos()
    {
        $this->m->content = <<<EOF
<audio controls>
    <source src="horse.ogg" type="audio/ogg">
    <source src="horse.mp3" type="audio/mpeg">
    Your browser does not support the audio tag.
</audio>
EOF;

        $expected = <<<EOF
<p><audio controls><source src="horse.ogg" type="audio/ogg"><source src="horse.mp3" type="audio/mpeg">Your browser does not support the audio tag.</audio></p>
EOF;

        $this->val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }

    /**
     * AutoParagraphValidator converts newlines to <br>s by default
     */
    public function testConvertBr()
    {
        $this->assertTrue($this->val->convertBr);

        $this->m->content = <<<EOF
This is one line.
This is another line.
EOF;

        $expected = <<<EOF
<p>This is one line.<br />
This is another line.</p>
EOF;

        $this->val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }

    /**
     * AutoParagraphValidator can be configured not to convert newlines to
     * <br>s
     */
    public function testNoConvertBr()
    {
        $this->val->convertBr = false;
        $this->assertFalse($this->val->convertBr);

        $this->m->content = <<<EOF
This is one line.
This is another line.
EOF;

        $expected = <<<EOF
<p>This is one line.
This is another line.</p>
EOF;

        $this->val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }

    /**
     * AutoParagraphValidator preserves newlines in <script>s and <style>s
     */
    public function testPreserveNewlinesInScriptStyles()
    {
        $expected = <<<EOF
<script>
alert("Hello!");
</script>
EOF;

        $this->m->content = $expected;

        $this->val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }

    /**
     * AutoParagraphValidator preserves newlines within tags
     */
    public function testPreserveNewlinesWithinTags()
    {
        $this->m->content = <<<EOF
<div id="test"
     class="test">
This is some content.
</div>
EOF;

        $expected = <<<EOF
<div id="test"
     class="test"><br />
This is some content.
</div>
EOF;

        $this->val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }
}
