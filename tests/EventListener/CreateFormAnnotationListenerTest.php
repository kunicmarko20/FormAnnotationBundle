<?php

namespace KunicMarko\FormAnnotationBundle\Tests\EventListener;

use Doctrine\Common\Annotations\Reader;
use KunicMarko\FormAnnotationBundle\Annotation\Form;
use KunicMarko\FormAnnotationBundle\EventListener\CreateFormAnnotationListener;
use KunicMarko\FormAnnotationBundle\Tests\Fixtures\FakeClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
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

        $createFormAnnotationListener->onKernelController($this->mockFilterControllerEvent('testBlank'));
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

    private function mockFilterControllerEvent(string $method)
    {
        $event = $this->createMock(FilterControllerEvent::class);

        $event->expects($this->once())
            ->method('getController')
            ->willReturn(
                [
                    FakeClass::class,
                    $method,
                ]
            );

        return $event;
    }

    /**
     * @dataProvider missingArgumentsData
     */
    public function testMissingArguments($object, $message)
    {
        $createFormAnnotationListener = new CreateFormAnnotationListener(
            $this->mockReader($object),
            $this->createMock(FormFactory::class),
            $this->createMock(RequestStack::class)
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $createFormAnnotationListener->onKernelController($this->mockFilterControllerEvent('testBlank'));
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

    public function testWrongParameterName()
    {
        $reader = $this->mockReader($annotation = $this->getValidAnnotation(new Form\Post()));

        $formFactory = $this->createMock(FormFactory::class);

        $requestStack = $this->createMock(RequestStack::class);

        $createFormAnnotationListener = new CreateFormAnnotationListener($reader, $formFactory, $requestStack);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "parameter" not found.');

        $createFormAnnotationListener->onKernelController($this->mockFilterControllerEvent('testBlank'));
    }

    private function getValidAnnotation($annotation): Form\AbstractFormAnnotation
    {
        $annotation->formType = 'formType';
        $annotation->parameter = 'parameter';

        return $annotation;
    }

    public function testMissingParameterType()
    {
        $reader = $this->mockReader($annotation = $this->getValidAnnotation(new Form\Post()));

        $formFactory = $this->createMock(FormFactory::class);

        $requestStack = $this->createMock(RequestStack::class);

        $createFormAnnotationListener = new CreateFormAnnotationListener($reader, $formFactory, $requestStack);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "parameter" is missing a type.');

        $createFormAnnotationListener->onKernelController($this->mockFilterControllerEvent('testWithParameter'));
    }

    public function testInvalidForm()
    {
        $reader = $this->mockReader($annotation = $this->getValidAnnotation(new Form\Post()));

        $formFactory = $this->mockForm($annotation);

        $requestStack = $this->mockRequest();

        $createFormAnnotationListener = new CreateFormAnnotationListener($reader, $formFactory, $requestStack);

        $event = $this->mockFilterControllerEvent('testWithParameterAndType');

        $event->expects($this->once())
            ->method('setController');

        $createFormAnnotationListener->onKernelController($event);
    }

    private function mockForm(Form\AbstractFormAnnotation $annotation, bool $isValid = false)
    {
        $formInterface = $this->createMock(FormInterface::class);

        $formInterface->expects($this->once())
            ->method('isValid')
            ->willReturn($isValid);

        $formInterface->expects($this->once())
            ->method('submit')
            ->with([], $annotation->clearMissing);

        $formFactory = $this->createMock(FormFactory::class);

        $formFactory->expects($this->once())
            ->method('create')
            ->willReturn($formInterface);

        return $formFactory;
    }

    private function mockRequest()
    {
        $request = $this->createMock(Request::class);

        $mockRequest = $this->createMock(ParameterBag::class);
        $mockRequest->expects($this->once())
            ->method('all')
            ->willReturn([]);

        $request->request = $mockRequest;

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        return $requestStack;
    }

    public function testValidForm()
    {
        $reader = $this->mockReader($annotation = $this->getValidAnnotation(new Form\Post()));

        $formFactory = $this->mockForm($annotation, true);

        $requestStack = $this->mockRequestWithAttributes($annotation);

        $createFormAnnotationListener = new CreateFormAnnotationListener($reader, $formFactory, $requestStack);

        $createFormAnnotationListener->onKernelController($this->mockFilterControllerEvent('testWithParameterAndType'));
    }

    private function mockRequestWithAttributes($annotation)
    {
        $request = $this->createMock(Request::class);

        $mockAttributes = $this->createMock(ParameterBag::class);
        $mockAttributes->expects($this->once())
            ->method('set')
            ->with($annotation->parameter);

        $request->attributes = $mockAttributes;

        $mockRequest = $this->createMock(ParameterBag::class);
        $mockRequest->expects($this->once())
            ->method('all')
            ->willReturn([]);

        $request->request = $mockRequest;

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        return $requestStack;
    }
}
