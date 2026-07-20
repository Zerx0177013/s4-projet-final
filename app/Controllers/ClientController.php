<?php

namespace App\Controllers;

class ClientController extends BaseController
{
    public function login(): string
    {
        return view('client/login');
    }

    public function dashboard(): string
    {
        return view('client/dashboard');
    }
}
