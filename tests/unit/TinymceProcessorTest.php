<?php
/**
 * TinyMCE Processor Test
 *
 * @copyright 2016 Cyberitas Technologies, LLC
 * @license LGPL v3
 */
namespace Cyberitas\TinymceProcessor\Tests\Unit;

use Cyberitas\TinymceProcessor\Tests\TestCase;
use Cyberitas\TinymceProcessor\TinymceProcessor;

/**
 * Test the TinyMCE processor.
 */
class TinymceProcessorTest extends TestCase
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \Cyberitas\TinymceProcessor\TinymceProcessor
     */
    protected $tmp;

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    protected function _before()
    {
        $this->tmp = new TinymceProcessor();
    }

    /**
     * TinymceProcessor extends yii\base\Model
     */
    public function testExtendsYiiBaseModel()
    {
        $this->assertInstanceOf('yii\base\Model', $this->tmp);
    }

    /**
     * TinymceProcessor content goes through EssenceValidator and has valid URLs
     * replaced with oEmbed media
     */
    public function testRunsEssenceValidator()
    {
        $input = "https://www.youtube.com/watch?v=9bZkp7q19f0";
        $expected = <<<EOF
<iframe width="480" height="270" src="https://www.youtube.com/embed/9bZkp7q19f0?feature=oembed" frameborder="0" allowfullscreen></iframe>
EOF;

        $output = $this->tmp->process($input);
        $this->assertEquals($expected, $output);
    }

    /**
     * TinymceProcessor content goes through HtmlPurifierValidator and has
     * unwanted tags and attributes removed
     */
    public function testRunsHtmlPurifierValidator()
    {
        $input = <<<EOF
This is some <a onclick="alert('Boo!')">bad content</a>.

<script>alert('Boo!')</script>
EOF;
        $expected = <<<EOF
<p>This is some <a>bad content</a>.</p>
EOF;

        $output = $this->tmp->process($input);
        $this->assertEquals($expected, $output);
    }

    /**
     * TinymceProcessor content goes through TexturizeValidator and has
     * appropriate formatting replacements performed
     */
    public function testRunsTexturizeValidator()
    {
        $input = <<<EOF
This is "some text..." (c) (r) (tm)
EOF;
        $expected = <<<EOF
<p>This is &#8220;some text&#8230;&#8221; &#169; &#174; &#8242;</p>
EOF;

        $output = $this->tmp->process($input);
        $this->assertEquals($expected, $output);
    }

    /**
     * TinymceProcessor configuration can enable, disable, and pass options to
     * individual validators.
     */
    public function testConfiguration()
    {
        $this->tmp->configure([
            'essence'   => false,
            'texturize' => true
        ]);

        $input = <<<EOF
Some of this should be processed...but some shouldn't.

https://www.youtube.com/watch?v=9bZkp7q19f0
EOF;
        $expected = <<<EOF
<p>Some of this should be processed&#8230;but some shouldn&#8217;t.</p>
<p>https://www.youtube.com/watch?v=9bZkp7q19f0</p>
EOF;

        $output = $this->tmp->process($input);
        $this->assertEquals($expected, $output);
    }
}
