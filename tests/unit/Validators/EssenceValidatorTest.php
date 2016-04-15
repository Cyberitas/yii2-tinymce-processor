<?php
/**
 * Essence Validator Test
 *
 * @copyright 2016 Cyberitas Technologies, LLC
 * @license LGPL v3
 */
namespace Cyberitas\TinymceProcessor\Tests\Unit;

use Cyberitas\TinymceProcessor\Tests\TestCase;
use Cyberitas\TinymceProcessor\Tests\Data\Models\TestModel;
use Cyberitas\TinymceProcessor\Validators\EssenceValidator;

/**
 * Test the Essence validator.
 */
class EssenceValidatorTest extends TestCase
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \yiiunit\data\validators\models\FakedValidationModel;
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

https://www.youtube.com/watch?v=9bZkp7q19f0
EOF;

        $this->m = new TestModel();
        $this->m->content = $content;
    }

    /**
     * EssenceValidator replaces supported URLs with oEmbed media
     */
    public function testValidateAttribute()
    {
        $expected = <<<EOF
This is some content that should be processed.

<iframe width="480" height="270" src="https://www.youtube.com/embed/9bZkp7q19f0?feature=oembed" frameborder="0" allowfullscreen></iframe>
EOF;

        $val = new EssenceValidator();
        $val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }

    /**
     * EssenceValidator can be configured
     */
    public function testProviderConfiguration()
    {
        $expected = $this->m->content;

        $val = new EssenceValidator([
            'essenceConfig' => [
                'filters' => [
                    'Youtube' => false
                ]
            ]
        ]);
        $val->validateAttribute($this->m, 'content');
        $this->assertEquals($expected, $this->m->content);
    }
}
