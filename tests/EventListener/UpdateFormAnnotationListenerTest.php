<?php

namespace KunicMarko\FormAnnotationBundle\Tests\EventListener;

use Doctrine\Common\Annotations\Reader;
use KunicMarko\FormAnnotationBundle\EventListener\UpdateFormAnnotationListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
class UpdateFormAnnotationListenerTest extends TestCase
{
    public function testNoController()
    {
        $reader = $this->createMock(Reader::class);
        $formFactory = $this->createMock(FormFactory::class);
        $requestStack = $this->createMock(RequestStack::class);

        $updateFormAnnotationListener = new UpdateFormAnnotationListener($reader, $formFactory, $requestStack);

        $event = $this->createMock(FilterControllerEvent::class);

        $event->expects($this->once())
            ->method('getController');

        $updateFormAnnotationListener->onKernelController($event);
    }
}
