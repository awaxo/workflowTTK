<?php

namespace App\Http\Composers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MenuComposer
{
    public function compose(View $view)
    {
        $view->with('user', Auth::user());

        $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
        $verticalMenuData = json_decode($verticalMenuJson);

        $user = User::find(Auth::id());
        $this->logMenuItems($verticalMenuData->menu, $user);

        $view->with('menuData', [$verticalMenuData]);
    }

    private function logMenuItems(&$menuItems, $user) {
        if (!is_array($menuItems)) {
            return;
        }
        if (!$user) {
            return;
        }

        foreach ($menuItems as $key => $menuItem) {
            if (isset($menuItem->authorize) && $menuItem->authorize == 'true' && $user->canViewMenuItem($menuItem->slug) == false) {
                unset($menuItems[$key]);
            }

            if (isset($menuItem->submenu)) {
                $this->logMenuItems($menuItem->submenu, $user);
            }
        }
    }
}
