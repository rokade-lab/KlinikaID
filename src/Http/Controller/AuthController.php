<?php

declare(strict_types=1);

namespace KlinikaID\Http\Controller;

use KlinikaID\Http\AppContext;
use KlinikaID\Http\Request;
use KlinikaID\Http\Response;

final class AuthController
{
    public const GENERIC_FAILURE = 'Nama pengguna atau kata sandi salah.';

    public function __construct(private readonly AppContext $app) {}

    public function showLogin(Request $request): Response
    {
        return $this->loginPage(null, '', 200);
    }

    public function login(Request $request): Response
    {
        $username = trim($request->postString('username'));
        $password = $request->postString('password');

        $user = $this->app->authenticator()->attempt($username, $password);
        if ($user === null) {
            $this->app->logger()->warning('auth.login.failure', ['user_id' => null, 'role' => null]);
            return $this->loginPage(self::GENERIC_FAILURE, $username, 401);
        }

        $this->app->session()->login($user->id);
        $this->app->setCurrentUser($user);
        $this->app->logger()->info('auth.login.success', ['user_id' => $user->id, 'role' => $user->role]);
        return Response::redirect('/dasbor', 303);
    }

    public function logout(Request $request): Response
    {
        $user = $this->app->currentUser();
        $this->app->session()->logout();
        $this->app->logger()->info('auth.logout', ['user_id' => $user?->id, 'role' => $user?->role]);
        return Response::redirect('/login', 303);
    }

    private function loginPage(?string $error, string $username, int $status): Response
    {
        $html = $this->app->view()->page('login', 'Masuk', ['error' => $error, 'username' => $username]);
        return Response::html($html, $status);
    }
}
