<?php

namespace Core;

interface ControllerInterace {
    public static function getAll();
    public static function getByParams(array $params);
    public static function post($data);
    public static function put($data);
    public static function delete($id);
}
