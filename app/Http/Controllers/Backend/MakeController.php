<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MakeDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreMakeRequest;
use App\Http\Requests\Backend\UpdateMakeRequest;
use App\Models\Make;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MakeController extends Controller
{
    /**
     * Display a listing of hardware makes / manufacturers.
     */
    public function index(Request $request, MakeDataTable $dataTable): mixed
    {
        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        $query = Make::query()
            ->withCount('products');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('slug', 'ilike', "%{$search}%")
                    ->orWhere('website', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        $makes = $query->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('backend.makes.index', compact('makes', 'dataTable'));
    }

    /**
     * Show the form for creating a new make.
     */
    public function create(): View
    {
        return view('backend.makes.create');
    }

    /**
     * Store a newly created make in storage.
     */
    public function store(StoreMakeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        if (empty($validated['slug'])) {
            $validated['slug'] = str()->slug($validated['name']);
        }
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('makes', 'public');
        }

        Make::create($validated);

        return redirect()->route('admin.makes.index')->with('success', 'Make created successfully.');
    }

    /**
     * Show the form for editing the specified make.
     */
    public function edit(Make $make): View
    {
        return view('backend.makes.edit', compact('make'));
    }

    /**
     * Update the specified make in storage.
     */
    public function update(UpdateMakeRequest $request, Make $make): RedirectResponse
    {
        $validated = $request->validated();
        if (empty($validated['slug'])) {
            $validated['slug'] = str()->slug($validated['name']);
        }
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->boolean('remove_logo')) {
            if ($make->logo && Storage::disk('public')->exists($make->logo)) {
                Storage::disk('public')->delete($make->logo);
            }
            $validated['logo'] = null;
        } elseif ($request->hasFile('logo')) {
            if ($make->logo && Storage::disk('public')->exists($make->logo)) {
                Storage::disk('public')->delete($make->logo);
            }
            $validated['logo'] = $request->file('logo')->store('makes', 'public');
        }

        $make->update($validated);

        return redirect()->route('admin.makes.index')->with('success', 'Make updated successfully.');
    }

    /**
     * Remove the specified make from storage.
     */
    public function destroy(Make $make): RedirectResponse
    {
        if ($make->products()->count() > 0) {
            return back()->with('error', 'Cannot delete make with associated products.');
        }

        if ($make->logo && Storage::disk('public')->exists($make->logo)) {
            Storage::disk('public')->delete($make->logo);
        }

        $make->delete();

        return redirect()->route('admin.makes.index')->with('success', 'Make deleted successfully.');
    }
}
