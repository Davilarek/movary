<?php declare(strict_types=1);

namespace Movary\Util;

class SessionWrapper
{
    public function destroy() : void
    {
        $_SESSION = array();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly'],
            );
        }

        session_destroy();
        session_regenerate_id();
    }

    public function find(string $key) : mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public function has(string $key) : bool
    {
        return isset($_SESSION[$key]) === true;
    }

    public function set(string $key, mixed $value) : void
    {
        $_SESSION[$key] = $value;
    }

    public function start() : void
    {
        session_start();
    }

    public function unset(string ...$keys) : void
    {
        foreach ($keys as $key) {
            unset($_SESSION[$key]);
        }
    }
}
