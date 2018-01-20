<?php

namespace KunicMarko\FormAnnotationBundle\Annotation\Form;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Patch extends AbstractFormAnnotation
{
    /**
     * @var bool
     */
    protected $clearMissing = false;

    public function getMethod() : string
    {
        return 'PATCH';
    }
}
