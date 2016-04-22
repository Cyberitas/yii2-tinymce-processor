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

    public function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    protected function _before()
    {
        $content = <<<EOF
This is some content that should be processed.

<iframe width="480" height="270" src="https://www.youtube.com/embed/9bZkp7q19f0?feature=oembed" frameborder="0" allowfullscreen></iframe>
EOF;

        $this->m = new TestModel();
        $this->m->content = $content;
    }

    public function testValidateAttribute()
    {
        $expected = <<<EOF
<p>This is some content that should be processed.</p>
<iframe width="480" height="270" src="https://www.youtube.com/embed/9bZkp7q19f0?feature=oembed" frameborder="0" allowfullscreen></iframe>
EOF;

        $val = new AutoParagraphValidator();
        $val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }
}
