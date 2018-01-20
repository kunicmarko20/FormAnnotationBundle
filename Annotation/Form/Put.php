<?php

namespace KunicMarko\FormAnnotationBundle\Annotation\Form;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Put extends AbstractFormAnnotation
{
    public function getMethod() : string
    {
        return 'PUT';
    }
}
