<?php

namespace KunicMarko\FormAnnotationBundle\Tests\EventListener;

use Doctrine\Common\Annotations\Reader;
use KunicMarko\FormAnnotationBundle\Annotation\Form;
use KunicMarko\FormAnnotationBundle\EventListener\CreateFormAnnotationListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
class CreateFormAnnotationListenerTest extends TestCase
{
    /**
     * @dataProvider getNotSupportedAnnotations
     */
    public function testNotSupported($annotation)
    {
        $formFactory = $this->createMock(FormFactory::class);
        $requestStack = $this->createMock(RequestStack::class);

        $createFormAnnotationListener = new CreateFormAnnotationListener(
            $this->mockReader($annotation),
            $formFactory,
            $requestStack
        );

        $createFormAnnotationListener->onKernelController($this->mockFilterControllerEvent());
    }

    public function getNotSupportedAnnotations()
    {
        return [
            [
                new Form\Put(),
            ],
            [
                new Form\Patch(),
            ],
        ];
    }

    private function mockReader($returnValue = null)
    {
        $reader = $this->createMock(Reader::class);
        $reader->expects($this->once())
            ->method('getMethodAnnotation')
            ->willReturn($returnValue);

        return $reader;
    }

    private function mockFilterControllerEvent()
    {
        $event = $this->createMock(FilterControllerEvent::class);

        $event->expects($this->once())
            ->method('getController')
            ->willReturn([
                new class() {
                    public function test()
                    {
                    }
                },
                'test', ]);

        return $event;
    }

    /**
     * @dataProvider missingArgumentsData
     */
    public function testMissingArguments($object, $message)
    {
        $formFactory = $this->createMock(FormFactory::class);
        $requestStack = $this->createMock(RequestStack::class);

        $createFormAnnotationListener = new CreateFormAnnotationListener(
            $this->mockReader($object),
            $formFactory,
            $requestStack
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $createFormAnnotationListener->onKernelController($this->mockFilterControllerEvent());
    }

    public function missingArgumentsData()
    {
        return [
            [
                new Form\Post(),
                'Annotation argument "formType" is missing.',
            ],
            [
                (function () {
                    $annotation = new Form\Post();
                    $annotation->formType = 'formType';

                    return $annotation;
                })(),
                'Annotation argument "parameter" is missing.',
            ],
        ];
    }
}
