<?php

namespace KunicMarko\FormAnnotationBundle\Tests\DependencyInjection;

use KunicMarko\FormAnnotationBundle\DependencyInjection\FormAnnotationExtension;
use KunicMarko\FormAnnotationBundle\EventListener\CreateFormAnnotationListener;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
class FormAnnotationExtensionTest extends AbstractExtensionTestCase
{
    public function testLoadsFormServiceDefinitionWhenColorPickerBundleIsRegistered()
    {
        $this->container->setParameter('kernel.bundles', ['FormAnnotationBundle' => 'whatever']);
        $this->load();
        $this->assertContainerBuilderHasService(
            'form_annotation.event_listener.create_form_annotation',
            CreateFormAnnotationListener::class
        );
    }
    
    protected function getContainerExtensions()
    {
        return [new FormAnnotationExtension()];
    }
}
