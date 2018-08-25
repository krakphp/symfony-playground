# Symfony Playground

Symfony playground enables developers to quickly test any code in their system and access any of the private services in their app.

## Installation

Install with composer at `krak/symfony-playground`.

Then you need to change the PlaygroundBundle in your `config/bundles.php` file to be dev only:

```php

return [
    // ...
    Krak\SymfonyPlayground\PlaygroundBundle::class => ['dev' => true],
];
```

## Usage

To enable the playground, you can create a playground.php in the project root: `%kernel.project_dir%/playground.php`.

This file needs to return a closure. Here's an example:

```php
<?php

/** this function is autowired, so type hint any service to access it here */
return function(App\Service\MyService $service, Psr\Log\LoggerInterface $log) {
    $log->info("Playing with my symfony app!");
};
```

You should be able to run this with: `./bin/console playground`.
