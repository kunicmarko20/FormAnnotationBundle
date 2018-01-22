<?php

namespace KunicMarko\FormAnnotationBundle\Tests\EventListener;

use Doctrine\Common\Annotations\Reader;
use KunicMarko\FormAnnotationBundle\Annotation\Form;
use KunicMarko\FormAnnotationBundle\EventListener\UpdateFormAnnotationListener;
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

    public function testNoAnnotation()
    {
        $formFactory = $this->createMock(FormFactory::class);
        $requestStack = $this->createMock(RequestStack::class);

        $updateFormAnnotationListener = new UpdateFormAnnotationListener(
            $this->mockReader(),
            $formFactory,
            $requestStack
        );

        $updateFormAnnotationListener->onKernelController($this->mockFilterControllerEvent());
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

    public function testNotSupported()
    {
        $formFactory = $this->createMock(FormFactory::class);
        $requestStack = $this->createMock(RequestStack::class);

        $updateFormAnnotationListener = new UpdateFormAnnotationListener(
            $this->mockReader(new Form\Post()),
            $formFactory,
            $requestStack
        );

        $updateFormAnnotationListener->onKernelController($this->mockFilterControllerEvent());
    }

    /**
     * @dataProvider missingArgumentsData
     */
    public function testMissingArguments($object, $message)
    {
        $formFactory = $this->createMock(FormFactory::class);
        $requestStack = $this->createMock(RequestStack::class);

        $updateFormAnnotationListener = new UpdateFormAnnotationListener(
            $this->mockReader($object),
            $formFactory,
            $requestStack
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $updateFormAnnotationListener->onKernelController($this->mockFilterControllerEvent());
    }

    public function missingArgumentsData()
    {
        return [
            [
                new Form\Put(),
                'Annotation argument "formType" is missing.',
            ],
            [
                (function () {
                    $annotation = new Form\Put();
                    $annotation->formType = 'formType';

                    return $annotation;
                })(),
                'Annotation argument "parameter" is missing.',
            ],
            [
                new Form\Patch(),
                'Annotation argument "formType" is missing.',
            ],
            [
                (function () {
                    $annotation = new Form\Patch();
                    $annotation->formType = 'formType';

                    return $annotation;
                })(),
                'Annotation argument "parameter" is missing.',
            ],
        ];
    }

    public function testWrongParameterName()
    {
        $reader = $this->mockReader($annotation = $this->getValidAnnotation(new Form\Put()));

        $formFactory = $this->createMock(FormFactory::class);

        $requestStack = $this->mockRequestAttribute($annotation);

        $updateFormAnnotationListener = new UpdateFormAnnotationListener($reader, $formFactory, $requestStack);

        $event = $this->mockFilterControllerEvent();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "parameter" not found.');
        $updateFormAnnotationListener->onKernelController($event);
    }

    private function mockRequestAttribute($annotation)
    {
        $request = $this->createMock(Request::class);

        $mockAttributes = $this->createMock(ParameterBag::class);
        $mockAttributes->expects($this->once())
            ->method('get')
            ->with($annotation->parameter)
            ->willReturn(null);

        $request->attributes = $mockAttributes;

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        return $requestStack;
    }

    public function testInvalidForm()
    {
        $reader = $this->mockReader($annotation = $this->getValidAnnotation(new Form\Put()));

        list($formFactory, $parameterValue) = $this->mockForm($annotation);

        $requestStack = $this->mockRequest($annotation, $parameterValue);

        $updateFormAnnotationListener = new UpdateFormAnnotationListener($reader, $formFactory, $requestStack);

        $event = $this->mockFilterControllerEvent();

        $event->expects($this->once())
            ->method('setController');

        $updateFormAnnotationListener->onKernelController($event);
    }

    private function getValidAnnotation($annotation): Form\AbstractFormAnnotation
    {
        $annotation->formType = 'formType';
        $annotation->parameter = 'parameter';

        return $annotation;
    }

    private function mockForm(Form\AbstractFormAnnotation $annotation, bool $isValid = false): array
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
            ->with($annotation->formType, $parameterValue = 'parameterValue', ['method' => $annotation->getMethod()])
            ->willReturn($formInterface);

        return [$formFactory, $parameterValue];
    }

    private function mockRequest($annotation, $parameterValue)
    {
        $request = $this->createMock(Request::class);

        $mockAttributes = $this->createMock(ParameterBag::class);
        $mockAttributes->expects($this->once())
            ->method('get')
            ->with($annotation->parameter)
            ->willReturn($parameterValue);

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

    public function testValidForm()
    {
        $reader = $this->mockReader($annotation = $this->getValidAnnotation(new Form\Patch()));

        list($formFactory, $parameterValue) = $this->mockForm($annotation, true);

        $requestStack = $this->mockRequest($annotation, $parameterValue);

        $updateFormAnnotationListener = new UpdateFormAnnotationListener($reader, $formFactory, $requestStack);

        $response = $updateFormAnnotationListener->onKernelController($this->mockFilterControllerEvent());

        $this->assertInstanceOf(Form\Patch::class, $response);
        $this->assertSame('parameter', $response->parameter);
    }
}
