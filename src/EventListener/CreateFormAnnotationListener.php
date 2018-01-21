<?php

namespace KunicMarko\FormAnnotationBundle\EventListener;

use KunicMarko\FormAnnotationBundle\Annotation\Form;
use KunicMarko\FormAnnotationBundle\Annotation\Form\AbstractFormAnnotation;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class CreateFormAnnotationListener extends AbstractFormAnnotationListener
{
    /**
     * @var \ReflectionMethod
     */
    private $reflectionMethod;

    /**
     * @var object
     */
    private $object;

    public function onKernelController(FilterControllerEvent $event)
    {
        $response = parent::onKernelController($event);

        if (!$response instanceof Form\Post) {
            return $response;
        }

        $this->replaceActionParameter($response);
    }

    private function replaceActionParameter(Form\Post $annotation) : void
    {
        $this->request->attributes->set($annotation->parameter, $this->object);
    }

    protected function getReflectionMethod(callable $controller): \ReflectionMethod
    {
        return $this->reflectionMethod = parent::getReflectionMethod($controller);
    }

    protected function isSupported(AbstractFormAnnotation $annotation): bool
    {
        return $annotation instanceof Form\Post;
    }

    protected function createForm(AbstractFormAnnotation $annotation): FormInterface
    {
        return $this->formFactory->create(
            $annotation->formType,
            $this->getObject($annotation),
            ['method' => $annotation->getMethod()]
        );
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return object
     */
    private function getObject(AbstractFormAnnotation $annotation)
    {
        $type = $this->getReflectionParameterType(
            $this->getReflectionParameter($annotation->parameter),
            $annotation->parameter
        );

        return $this->object = new $type();
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function getReflectionParameterType(\ReflectionParameter $reflectionParameter, string $parameter) : string
    {
        if (!($type = $reflectionParameter->getType())) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Parameter "%s" in "%s" method is missing a type.',
                    $parameter,
                    $this->reflectionMethod->getName()
                )
            );
        }

        return $type->getName();
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function getReflectionParameter(string $parameter) : \ReflectionParameter
    {
        foreach ($this->reflectionMethod->getParameters() as $reflectionParameter) {
            if ($reflectionParameter->getName() === $parameter) {
                return $reflectionParameter;
            }
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Parameter "%s" not found in "%s" method.',
                $parameter,
                $this->reflectionMethod->getName()
            )
        );
    }
}
