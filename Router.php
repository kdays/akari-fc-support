<?php

namespace AkariFC;

use Akari\system\router\Router as BaseRouter;
use Akari\system\util\TextUtil;

class Router extends BaseRouter{

    public function getParameters() {
        return $this->params ?? [];
    }

}