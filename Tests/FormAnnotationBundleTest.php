<?php

namespace KunicMarko\FormAnnotationBundle\Tests;

use KunicMarko\FormAnnotationBundle\FormAnnotationBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
class FormAnnotationBundleTest extends TestCase
{
    /**
     * @var FormAnnotationBundle
     */
    private $bundle;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->bundle = new FormAnnotationBundle();
    }

    public function testBundle()
    {
        $this->assertInstanceOf(Bundle::class, $this->bundle);
    }
}
