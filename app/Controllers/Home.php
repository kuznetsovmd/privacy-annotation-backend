<?php

namespace App\Controllers;

use Engine\Services\AuthService as Auth;
use Engine\Request;
use Engine\View;

/**
 * Home.php
 *
 * Controller class for loading home page.
 */
class Home
{

    /**
     * Goes to home page.
     *
     * @param Request $request
     */
    public static function toHomePage(Request $request)
    {
        $request->view = new View('home.php', [
            'title' => 'Home',
            'id' => Auth::authenticated(),
        ]);
    }

}
