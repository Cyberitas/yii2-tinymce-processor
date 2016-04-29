<?php
/**
 * Texturize Validator Test
 */
namespace Cyberitas\TinymceProcessor\Tests\unit\Validators;

use Cyberitas\TinymceProcessor\Tests\TestCase;
use Cyberitas\TinymceProcessor\Tests\_support\models\TestModel;
use Cyberitas\TinymceProcessor\Validators\TexturizeValidator;

/**
 * Test the texturize validator.
 */
class TexturizeValidatorTest extends TestCase
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
This--is---some "content" 'that' sho'uld be processed.

2x2

<!-- Don't process this HTML comment. -->
<strong>Do process this...</strong>
EOF;

        $this->m = new TestModel();
        $this->m->content = $content;
    }

    public function testTexturize()
    {
        $expected = <<<EOF
This&#8212;is&#8211;some &#8220;content&#8221; &#8216;that&#8217; sho&#8217;uld be processed.

2&#215;2

<!-- Don't process this HTML comment. -->
<strong>Do process this&#8230;</strong>
EOF;

        $val = new TexturizeValidator();
        $val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }
}
