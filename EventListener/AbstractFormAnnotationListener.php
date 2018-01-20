<?php

namespace KunicMarko\FormAnnotationBundle\EventListener;

use KunicMarko\FormAnnotationBundle\Annotation\Form\AbstractFormAnnotation;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
abstract class AbstractFormAnnotationListener
{
    private $reader;
    protected $formFactory;
    protected $request;

    public function __construct(Reader $reader, FormFactory $formFactory, RequestStack $requestStack)
    {
        $this->reader = $reader;
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!\is_array($controller = $event->getController())) {
            return;
        }

        if (!($annotation = $this->getAnnotation($controller)) || !$this->isSupported($annotation)) {
            return;
        }

        $annotation->validate();

        $form = $this->createForm($annotation);

        $form->submit($this->request->request->all(), $annotation->clearMissing);

        if (!$form->isValid()) {
            return $event->setController(
                function () use ($form) {
                    return $form;
                }
            );
        }

        return $annotation;
    }

    private function getAnnotation(callable $controller) : ?AbstractFormAnnotation
    {
        return $this->reader
            ->getMethodAnnotation(
                $this->getReflectionMethod($controller),
                AbstractFormAnnotation::class
            );
    }

    protected function getReflectionMethod(callable $controller) : \ReflectionMethod
    {
        list($class, $method) = $controller;

        return new \ReflectionMethod($class, $method);
    }

    abstract protected function isSupported(AbstractFormAnnotation $annotation) : bool;
    abstract protected function createForm(AbstractFormAnnotation $annotation) : FormInterface;
}
