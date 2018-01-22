<?php

namespace KunicMarko\FormAnnotationBundle\Tests\Fixtures;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
class FakeClass
{
    public static function testBlank()
    {
    }

    public static function testWithParameter($parameter)
    {
    }

    public static function testWithParameterAndType(FakeClass $parameter)
    {
    }
}
