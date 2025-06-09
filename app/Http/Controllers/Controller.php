<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base controller for the application.
 * 
 * This controller serves as a base for all other controllers in the application,
 * providing common functionality such as authorization and validation.
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
