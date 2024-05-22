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
        // TODO: not yet working
        
        /*$verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
        $verticalMenuData = json_decode($verticalMenuJson); // Decode as objects

        $user = User::find(Auth::id());

        // Recursive function to filter menu items
        $filterMenuItems = function ($menuItems) use ($user, &$filterMenuItems) {
            foreach ($menuItems as $key => $menuItem) {
                if (isset($menuItem->authorizable) && $menuItem->authorizable == true && !$user->canViewMenuItem($menuItem->slug)) {
                    unset($menuItems->$key);
                } elseif (isset($menuItem->submenu)) {
                    $menuItem->submenu = $filterMenuItems($menuItem->submenu);
                    // If the submenu becomes empty after filtering, remove the submenu property
                    if (empty((array)$menuItem->submenu)) {
                        unset($menuItem->submenu);
                    }
                }
            }

            return $menuItems;
        };

        $verticalMenuData->menu = $filterMenuItems($verticalMenuData->menu);

        // Share filtered menu data to the view
        $view->with('menuData', $verticalMenuData);*/
    }
}
