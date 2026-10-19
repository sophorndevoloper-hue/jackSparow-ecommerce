<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\StorefrontMenu;
use App\Models\UserActivity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorefrontMenuController extends Controller
{
    /**
     * Display a listing of storefront menus by location.
     */
    public function index(Request $request): View
    {
        $location = $request->query('location', 'main');
        if (! in_array($location, ['top', 'main', 'footer'], true)) {
            $location = 'main';
        }

        // Fetch parent items with all children ordered
        $menus = StorefrontMenu::location($location)
            ->topLevel()
            ->with(['allChildren'])
            ->orderBy('sort_order', 'asc')
            ->get();

        // Get list of possible parent options for this location
        $parentOptions = StorefrontMenu::location($location)
            ->topLevel()
            ->orderBy('sort_order', 'asc')
            ->get();

        // Counts for tab badges
        $counts = [
            'top' => StorefrontMenu::location('top')->count(),
            'main' => StorefrontMenu::location('main')->count(),
            'footer' => StorefrontMenu::location('footer')->count(),
        ];

        return view('backend.website-menus.index', compact(
            'menus',
            'location',
            'parentOptions',
            'counts'
        ));
    }

    /**
     * Store a newly created storefront menu item in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'location' => ['required', 'string', 'in:top,main,footer'],
            'parent_id' => ['nullable', 'exists:storefront_menus,id'],
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:500'],
            'target' => ['required', 'string', 'in:_self,_blank'],
            'icon' => ['nullable', 'string', 'max:100'],
            'badge' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $menu = StorefrontMenu::create($validated);
        StorefrontMenu::flushCache();

        UserActivity::log(
            'CREATE',
            'Website Menus',
            "Added storefront menu item '{$menu->title}' in '{$menu->location}' menu",
            $menu,
            ['location' => $menu->location, 'parent_id' => $menu->parent_id]
        );

        return redirect()->route('admin.storefront-menus.index', ['location' => $menu->location])
            ->with('success', "Menu item '{$menu->title}' created successfully.");
    }

    /**
     * Update the specified storefront menu item in storage.
     */
    public function update(Request $request, StorefrontMenu $storefront_menu): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:storefront_menus,id'],
            'url' => ['required', 'string', 'max:500'],
            'target' => ['required', 'string', 'in:_self,_blank'],
            'icon' => ['nullable', 'string', 'max:100'],
            'badge' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable'],
        ]);

        // Prevent setting self as parent
        if (isset($validated['parent_id']) && (int) $validated['parent_id'] === $storefront_menu->id) {
            $validated['parent_id'] = null;
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $storefront_menu->update($validated);
        StorefrontMenu::flushCache();

        UserActivity::log(
            'UPDATE',
            'Website Menus',
            "Updated storefront menu item '{$storefront_menu->title}'",
            $storefront_menu,
            ['location' => $storefront_menu->location]
        );

        return redirect()->route('admin.storefront-menus.index', ['location' => $storefront_menu->location])
            ->with('success', "Menu item '{$storefront_menu->title}' updated successfully.");
    }

    /**
     * Remove the specified storefront menu item from storage.
     */
    public function destroy(StorefrontMenu $storefront_menu): RedirectResponse
    {
        $title = $storefront_menu->title;
        $location = $storefront_menu->location;

        $storefront_menu->delete();
        StorefrontMenu::flushCache();

        UserActivity::log(
            'DELETE',
            'Website Menus',
            "Deleted storefront menu item '{$title}' from '{$location}' menu",
            null,
            ['location' => $location]
        );

        return redirect()->route('admin.storefront-menus.index', ['location' => $location])
            ->with('success', "Menu item '{$title}' deleted successfully.");
    }

    /**
     * Toggle active status of a menu item.
     */
    public function toggleActive(StorefrontMenu $storefront_menu): RedirectResponse
    {
        $storefront_menu->update(['is_active' => ! $storefront_menu->is_active]);
        StorefrontMenu::flushCache();

        $status = $storefront_menu->is_active ? 'enabled' : 'disabled';

        UserActivity::log(
            'STATUS_CHANGE',
            'Website Menus',
            "{$status} storefront menu item '{$storefront_menu->title}'",
            $storefront_menu
        );

        return redirect()->back()->with('success', "Menu item '{$storefront_menu->title}' has been {$status}.");
    }

    /**
     * Update sort orders via batch array.
     */
    public function updateSort(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:storefront_menus,id'],
            'items.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($request->input('items') as $item) {
            StorefrontMenu::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        StorefrontMenu::flushCache();

        return response()->json(['success' => true, 'message' => 'Sort orders updated successfully.']);
    }
}
