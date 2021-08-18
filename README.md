<h1 align="center">🚀 php-blueprints</h1>

**php-blueprints** is a library that can make your life easier

This library provides functionality to create html/php blueprints and use them in your other files

## Installation

```
composer require krypt0nn/php-blueprints
```

## Example

File `input/blank.php`

```html
<html>
    <head>
        <title>Example blueprint</title>
    </head>

    <body>
        @section(body)
    </body>
</html>
```

File `input/index.php`

```html
@include(blank)

@section(body)
    <p>Hello, World!</p>
    <p>Hello, World!</p>
    <p>Hello, World!</p>
    <p>Hello, World!</p>
@end
```

Run this code

```php
<?php

require 'vendor/autoload.php';

use Blueprints\Blueprints;

Blueprints::processDir (__DIR__ .'/input', __DIR__ .'/output');
```

And in folder `output` will appear file `index.php` with this content:

```html
<html>
    <head>
        <title>Example blueprint</title>
    </head>

    <body>
        <!-- @section(body) -->
        <p>Hello, World!</p>
        <p>Hello, World!</p>
        <p>Hello, World!</p>
        <p>Hello, World!</p>
    </body>
</html>
```

You can see this example in [test](/test) folder

<br>

Author: [Nikita Podvirnyy](https://vk.com/technomindlp)