<?php

namespace AkariFC;

use Akari\Core;
use Akari\system\router\Dispatcher as BaseDispatcher;

class Dispatcher extends BaseDispatcher {

    public function getAppActionNs() {
        $config = $this->_getConfigValue('bindDomain', '');
        if (!empty($config)) {
            return $config::handleAppActionNs($this->request);
        }

        return Core::$appNs . NAMESPACE_SEPARATOR . 'action' ;
    }

}