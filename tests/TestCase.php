<?php
namespace Cyberitas\TinymceProcessor\Tests;

use Codeception\TestCase\Test;
use yii\di\Container;
use yii\helpers\ArrayHelper;
use Yii;

abstract class TestCase extends Test
{
    protected function tearDown()
    {
        parent::tearDown();
        $this->destroyApplication();
    }

    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
        ], $config));
    }

    protected function mockWebApplication($config = [], $appClass = '\yii\web\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'components' => [
                'request' => [
                    'cookieValidationKey' => '3Pa63mXm9t4WnY4hCryfab6rnKZ4ghHw',
                    'scriptFile' => __DIR__ . '/index.php',
                    'scriptUrl' => '/index.php',
                ],
            ]
        ], $config));
    }

    protected function destroyApplication()
    {
        Yii::$app = null;
        Yii::$container = new Container();
    }

    protected function debug($data)
    {
        return fwrite(STDERR, print_r($data, TRUE));
    }
}
