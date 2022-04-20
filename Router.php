<?php

namespace AkariFC;

use Akari\system\router\Router as BaseRouter;
use Akari\system\util\TextUtil;

class Router extends BaseRouter implements ICanFree {

    public function getParameters() {
        return $this->params ?? [];
    }

    public function freeRes() {
        $this->params = [];
        // TODO: Implement freeRes() method.
    }


}