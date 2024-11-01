<?php

namespace Core;

interface ControllerInterace {
    public static function getAll(int $id_academia);
    public static function getByParams($params);
    public static function post($data);
    public static function put($data);
    public static function delete(int $id, int $id_academia);
}
