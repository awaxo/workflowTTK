<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * MiscError handles the display of miscellaneous error pages.
 *
 * This controller is responsible for rendering error pages with a blank layout.
 */
class MiscError extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.pages.pages-misc-error', ['pageConfigs' => $pageConfigs]);
  }
}
