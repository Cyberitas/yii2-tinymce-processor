<?php
/**
 * HTMLPurifier Validator Test
 *
 * @copyright 2016 Cyberitas Technologies, LLC
 * @license LGPL v3
 */
namespace Cyberitas\TinymceProcessor\Tests\unit\Validators;

use Cyberitas\TinymceProcessor\Tests\TestCase;
use Cyberitas\TinymceProcessor\Tests\_support\models\TestModel;
use Cyberitas\TinymceProcessor\Validators\HtmlPurifierValidator;

/**
 * Test the HTMLPurifier validator.
 */
class HtmlPurifierValidatorTest extends TestCase
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
This should be allowed.

<script>alert("This shouldn't.");</script>

<iframe src="http://nor-should-this/iframe"></iframe>
EOF;

        $this->m = new TestModel(['content' => $content]);
    }

    /**
     * HtmlPurifierValidator runs HTMLPurifier on the content
     */
    public function testPurify()
    {
        $expected = <<<EOF
This should be allowed.


EOF;

        $val = new HtmlPurifierValidator();
        $val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }

    /**
     * HtmlPurifierValidator can pass configuration to HTMLPurifier
     */
    public function testConfigurePurify()
    {
        $config = [
            'HTML.SafeIframe' => true,
            'URI.SafeIframeRegexp' => '%^http://nor-should-this/iframe$%',
        ];

        $expected = <<<EOF
This should be allowed.

<iframe src="http://nor-should-this/iframe"></iframe>
EOF;

        $val = new HtmlPurifierValidator(['purifierConfig' => $config]);
        $val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }
}
