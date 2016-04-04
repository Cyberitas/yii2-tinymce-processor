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
        $output = $this->tmp->process("https://www.youtube.com/watch?v=9bZkp7q19f0");
        $expected = <<<EOF
<iframe width="480" height="270" src="https://www.youtube.com/embed/9bZkp7q19f0?feature=oembed" frameborder="0" allowfullscreen></iframe>
EOF;

        $this->assertTrue($this->tmp->validate());
        $this->assertEquals($expected, $output);
    }
}
