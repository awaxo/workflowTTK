<?php

namespace App\Http\Composers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class MenuComposer
{
    public function compose(View $view)
    {
        $view->with('user', Auth::user());

        $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
        $verticalMenuData = json_decode($verticalMenuJson);

        $user = User::find(Auth::id());
        $this->updateMenuItems($verticalMenuData->menu, $user);

        $view->with('menuData', [$verticalMenuData]);
    }

    private function updateMenuItems(&$menuItems, $user) {
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
                $this->updateMenuItems($menuItem->submenu, $user);
            }
        }
    }
}
