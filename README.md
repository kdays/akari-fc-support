# akari-fc-support
Akari Framework FC Support


Sample FC:
```php
<?php

use Akari\system\event\Event;
use Akari\system\result\Result;
use Akari\system\router\Dispatcher;
use RingCentral\Psr7\Response;

include("const.php");
define('APP_ENV', 'FC');

require("vendor/autoload.php");

function handler($request, $context): Response {
    $app = \Akari\Core::initApp(__DIR__, APP_NS);
    $di = \Akari\system\ioc\DI::getDefault();

    \AkariFC\Request::setFcRequest($request);

    $di->setShared('request', AkariFC\Request::class);
    $di->setShared('response', AkariFC\Response::class);
    $di->setShared('cookie', \AkariFC\Cookie::class);
    $di->setShared('router', \AkariFC\Router::class);
    $di->setShared('dispatcher', \AkariFC\Dispatcher::class);

    $uri = $app->router->resolveURI();
    $toParameters = $app->router->getParameters();
    $app->dispatcher->initFromUrl($uri, $toParameters);

    Event::fire(Dispatcher::EVENT_APP_START, []);
    $result = $app->dispatcher->dispatch();
    if ($result instanceof Result) {
        $app->processor->process($result);
    }
    Event::fire(Dispatcher::EVENT_APP_END, []);

    /** @var \AkariFC\Response $appResponse */
    $appResponse = $app->response;

    return $appResponse->toFcResponse();
}
```
