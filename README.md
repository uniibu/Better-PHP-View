[![Build Status](https://travis-ci.org/geggleto/PHP-View.svg?branch=master)](https://travis-ci.org/geggleto/PHP-View)

## Better-PHP-View

This is a renderer for rendering PHP view scripts into a PSR-7 Response object. It works well with Slim Framework 3.

## This Version

This version is a fork from the original PHP-view https://github.com/slimphp/PHP-View

This version also adds optional escaping using zend-escaper (https://github.com/zendframework/zend-escaper) and the ability to include other views using $PHPview->include('TEMPLATE NAME');

Included templates must reside on the same template directory.

To use the escape function, just add the type of escaping when calling $PHPview->render ex. $PHPview->render($response,'template.php',['args' => 'value'],'html')

If escape type is left out, nothing will be escaped. It will only escape the values of the associative arrays.

### Cross-site scripting (XSS) risks (optional on this version)

Note that PHP-View has no built-in mitigation from XSS attacks. It is the developer's responsibility to use `htmlspecialchars()` or a component like [zend-escaper](https://github.com/zendframework/zend-escaper). Alternatively, consider  [Twig-View](https://github.com/slimphp/Twig-View).



## Templates
You may use `$this` inside your php templates. `$this` will be the actual PhpRenderer object will allow you to render sub-templates

## Installation

Install with [Composer](http://getcomposer.org):

    composer require uniibu/better-php-view:dev-master


## Usage with Slim 3 (this version)

```php
use Slim\Views\PhpRenderer;

include "vendor/autoload.php";

$app = new Slim\App();
$container = $app->getContainer();
$container['renderer'] = new PhpRenderer("./templates");

$app->get('/hello/{name}', function ($request, $response, $args) {
    return $this->renderer->render($response, "/hello.php", $args, 'html');
});

$app->run();
```

## Usage with any PSR-7 Project
```php
//Construct the View
$phpView = new PhpRenderer("./path/to/templates");

//Render a Template
$response = $phpView->render(new Response(), "/path/to/template.php", $yourData, 'html');
```
## Escaping Options (this version) check https://github.com/zendframework/zend-escaper for reference

> 'html' = zend->escapeHtml
> 'attr' = zend->escapeHtmlAttr
> 'url' = zend->escapeUrl
> 'js' = zend->escapeJs
> 'css' = zend->escapeCss

## Template Variables (this version)

You can now add variables and escape type to your renderer that will be available to all templates you render.

```php
// via the constructor
$templateVariables = [
    "title" => "Title"
];
$phpView = new PhpRenderer("./path/to/templates", $templateVariables, 'html');

// or setter
$phpView->setAttributes($templateVariables);

// or individually
$phpView->addAttribute($key, $value);
```

Data passed in via `->render()` takes precedence over attributes.
```php
$templateVariables = [
    "title" => "Title"
];
$phpView = new PhpRenderer("./path/to/templates", $templateVariables, 'js');

//...

$phpView->render($response, $template, [
    "title" => "My Title"
], 'attr');
// In the view above, the $title will be "My Title" and not "Title"
```

## Exceptions
`\RuntimeException` - if template does not exist

`\InvalidArgumentException` - if $data contains 'template'
