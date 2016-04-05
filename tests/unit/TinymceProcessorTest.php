<?php
/**
 * TinyMCE Processor Test
 *
 * @copyright 2016 Cyberitas Technologies, LLC
 * @license LGPL-3.0+
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
     * TinymceProcessor content goes through TexturizeValidator and has
     * appropriate formatting replacements performed
     */
    public function testRunsTexturizeValidator()
    {
        $input = "This is some text... (c) (r) (tm)";
        $expected = <<<EOF
This is some text&#8230; &#169; &#174; &#8242;
EOF;

        $output = $this->tmp->process($input);
        $this->assertEquals($expected, $output);
    }
}
