Form Annotation Bundle
============
[![Build Status](https://travis-ci.org/kunicmarko20/FormAnnotationBundle.svg?branch=master)](https://travis-ci.org/kunicmarko20/FormAnnotationBundle)
[![StyleCI](https://styleci.io/repos/118287935/shield?branch=master)](https://styleci.io/repos/118287935)
[![Coverage Status](https://coveralls.io/repos/github/kunicmarko20/FormAnnotationBundle/badge.svg)](https://coveralls.io/github/kunicmarko20/FormAnnotationBundle)

Adds Form Annotations that helps you avoid boilerplate code when using forms.

Documentation
-------------

* [Installation](#installation)
* [How to use](#how-to-use)
* [Annotations](#annotations)

## Installation

**1.**  Add dependency with composer

```
composer require kunicmarko/form-annotation-bundle
```

**2.** Register the bundle in your Kernel

```
$bundles = array(
    // ...
    new KunicMarko\FormAnnotationBundle\FormAnnotationBundle(),
);
```

## How to use

First select the [annotation](#annotations) you want to use and add it
to your controller action, you have to provide form class and parameter name,
also parameter has to have a type.

While [FOSRestBundle](https://github.com/FriendsOfSymfony/FOSRestBundle) is not dependency,
my examples are with FOSRestBundle and this is mainly used with it.

Before:

```
public function createAction(Request $request, UserService $userService)
{
    $form = $this->createForm(CreateUserType::class, $user = new User());

    $form->submit($request->request->all());

    if ($form->isValid()) {
        $userService->createUser($user);

        return View::create($user, Response::HTTP_CREATED);
    }

    return View::create($form);
}
```

After:

```
use KunicMarko\FormAnnotationBundle\Annotation\Form;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Response;

 /**
 * @Form\Post(
 *     formType=CreateUserType::class,
 *     parameter="user"
 * )
 *
 * @View(statusCode=Response::HTTP_CREATED)
 */
public function createAction(User $user, UserService $userService)
{
    $userService->createUser($user);

    return $user;
}
```

The Request enters controller action only if everything in form is valid,
else it will return validation  errors.

## Annotations

There are 3 options you can set when adding annotation to your action:

* ``formType`` - FQCN of your form type.
* ``parameter`` - parameter name you will be using in your action. (parameter has to have a type)
* ``clearMissing`` - if some value is not sent this will by default set it to null for ``POST``/``PUT`` but
for ``PATCH`` this value will be ignored. You can control this on your own if you want.

### Post

```
use KunicMarko\FormAnnotationBundle\Annotation\Form;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Response;

 /**
 * @Form\Post(
 *     formType=CreateUserType::class,
 *     parameter="user"
 * )
 *
 * @View(statusCode=Response::HTTP_CREATED)
 */
public function createAction(
    User $user,
    UserService $userService
) {
    $userService->createUser($user);

    return $user;
}
```
In ``POST`` we take your parameter and from type you provided. We create
new Object and populate data from Request and if everything is valid we
populate the parameter in your action and we let Request enter your action
else we return form errors.

### Patch/Put

```
use KunicMarko\FormAnnotationBundle\Annotation\Form;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Response;


 /**
 * @Form\Put(
 *     formType=UpdateUserType::class,
 *     parameter="user"
 * )
 *
 * @View(statusCode=Response::HTTP_OK)
 */
public function updateAction(
    User $user,
    UserService $userService
) {
    $userService->updateUser($user);

    return $user;
}
```

Here we depend on Symfony ParamConverter and we expect that the parameter you
provided is already a populated object. If you add ``{id}`` parameter
to your route and type-hint it with Doctrine Entity, Symfony ParamConverter
will already do that. If everything is valid we let Request enter your action
else we return form errors.
