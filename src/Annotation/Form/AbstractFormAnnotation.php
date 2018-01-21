<?php

namespace KunicMarko\FormAnnotationBundle\Annotation\Form;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
abstract class AbstractFormAnnotation
{
    /**
     * @var string
     */
    public $formType;

    /**
     * @var string
     */
    public $parameter;

    /**
     * @var bool
     */
    public $clearMissing = true;

    /**
     * @throws \InvalidArgumentException
     */
    public function validate() : void
    {
        if (!$this->formType) {
            throw new \InvalidArgumentException('Annotation argument "formType" is missing.');
        }

        if (!$this->parameter) {
            throw new \InvalidArgumentException('Annotation argument "parameter" is missing.');
        }
    }

    abstract public function getMethod() : string;
}
