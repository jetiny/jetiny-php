<?php

namespace Jetiny;

class Action
{
    
    public function __get($module) {
        return App::instance()->{$module};
    }
    
}
