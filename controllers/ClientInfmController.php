<?php
/**
 * ClientInfmController
 *
 * MVC controller for the client_infm page.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/interceptor.php';

class ClientInfmController
{
    public static function index(): void
    {
        require __DIR__ . '/../views/client_infm.php';
    }
}

ClientInfmController::index();


