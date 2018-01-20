<?php

namespace KunicMarko\FormAnnotationBundle\EventListener;

use KunicMarko\FormAnnotationBundle\Annotation\Form;
use KunicMarko\FormAnnotationBundle\Annotation\Form\AbstractFormAnnotation;
use Symfony\Component\Form\FormInterface;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class UpdateFormAnnotationListener extends AbstractFormAnnotationListener
{
    protected function isSupported(AbstractFormAnnotation $annotation): bool
    {
        return $annotation instanceof Form\Put || $annotation instanceof Form\Patch;
    }

    protected function createForm(AbstractFormAnnotation $annotation): FormInterface
    {
        return $this->formFactory->create(
            $annotation->formType,
            $this->request->attributes->get($annotation->parameter),
            ['method' => $annotation->getMethod()]
        );
    }
}
