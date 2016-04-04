<?php
namespace Cyberitas\TinymceProcessor\Tests\Unit;

use Cyberitas\TinymceProcessor\Tests\TestCase;
use Cyberitas\TinymceProcessor\TinymceProcessor;

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
        $this->mockWebApplication();
    }

    protected function _before()
    {
        $this->tmp = new TinymceProcessor();
        $this->tmp->content = "https://www.youtube.com/watch?v=9bZkp7q19f0";
    }

    public function testExtendsYiiBaseModel()
    {
        $this->assertInstanceOf('yii\base\Model', $this->tmp);
    }

    public function testRunsEssenceValidator()
    {
        $content = <<<EOF
<iframe width="480" height="270" src="https://www.youtube.com/embed/9bZkp7q19f0?feature=oembed" frameborder="0" allowfullscreen></iframe>
EOF;

        $this->assertTrue($this->tmp->validate());
        $this->assertEquals($content, $this->tmp->content);
    }
}
