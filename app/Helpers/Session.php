<?php

namespace App\Helpers;

class Session
{
    private static $instance;

    private function __construct()
    {
        $this->startSession();
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function startSession()
    {
        session_start();
    }

    static public function put(string $key, $value)
    {
        $_SESSION[$key] = $value;
    }

    static public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    static public function get($key)
    {
        return $_SESSION[$key];
    }

    static public function forget($key)
    {
        unset($_SESSION[$key]);
    }
}