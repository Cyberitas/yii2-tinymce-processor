<?php
/**
 * TinyMCE Asset Bundle Test
 */
namespace Cyberitas\TinymceProcessor\Tests\unit\Assets;

use Cyberitas\TinymceProcessor\Tests\TestCase;
use Cyberitas\TinymceProcessor\Assets\TinymceAssets;

/**
 * Test the TinyMCE asset bundle.
 */
class TinymceAssetsTest extends TestCase
{
    /**
     * @var UnitTester
     */
    protected $tester;

    public function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    /**
     * TinymceAssets includes the default skin CSS
     */
    public function testCss()
    {
        $ab = new TinymceAssets();

        $this->assertCount(1, $ab->css);
        $this->assertContains('skins/lightgray/skin.min.css', $ab->css);
    }

    /**
     * TinymceAssets includes the CSS for the specified skin
     */
    public function testCssSkin()
    {
        $ab = new TinymceAssets(['skin' => 'test']);

        $this->assertCount(1, $ab->css);
        $this->assertContains('skins/test/skin.min.css', $ab->css);
    }

    /**
     * TinymceAssets includes the JS
     */
    public function testJs()
    {
        $ab = new TinymceAssets();

        $this->assertCount(2, $ab->js);
        $this->assertContains('tinymce.min.js', $ab->js);
        $this->assertContains('themes/modern/theme.min.js', $ab->js);
    }

    /**
     * TinymceAssets includes the non-minified assets in debug mode
     */
    public function testDebug()
    {
        $ab = new TinymceAssets(['debug' => true]);

        $this->assertContains('skins/lightgray/skin.css', $ab->css);
        $this->assertContains('tinymce.js', $ab->js);
        $this->assertContains('themes/modern/theme.js', $ab->js);
    }
}
