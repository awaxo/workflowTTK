<?php

namespace App\Http\Composers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

/*
 * MenuComposer is responsible for composing the menu data for views.
 * It reads the vertical menu configuration from a JSON file and filters
 * the menu items based on the user's permissions.
 */
class MenuComposer
{
    /**
     * Compose the view with user and menu data.
     *
     * @param View $view
     */
    public function compose(View $view)
    {
        $view->with('user', Auth::user());

        $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
        $verticalMenuData = json_decode($verticalMenuJson);

        $user = User::find(Auth::id());
        $this->updateMenuItems($verticalMenuData->menu, $user);

        $view->with('menuData', [$verticalMenuData]);
    }

    /**
     * Recursively update menu items based on user permissions.
     *
     * @param array $menuItems
     * @param User|null $user
     */
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
