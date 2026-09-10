<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * Display the product media manager gallery and upload panel.
     */
    public function index(Request $request): View
    {
        $selectedProductId = $request->input('product_id');

        $products = Product::query()->with('images')->orderBy('name')->get();

        $imagesQuery = ProductImage::query()->with('product');

        if ($selectedProductId) {
            $imagesQuery->where('product_id', $selectedProductId);
        }

        $images = $imagesQuery->latest()->paginate(24)->withQueryString();

        $selectedProduct = $selectedProductId ? Product::with('images')->find($selectedProductId) : null;

        return view('backend.products.images.index', compact('products', 'images', 'selectedProduct', 'selectedProductId'));
    }

    /**
     * Save staged gallery updates (upload new, delete marked, set primary cover) in a single transaction.
     */
    public function saveGallery(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'delete_image_ids' => ['nullable', 'array'],
            'delete_image_ids.*' => ['integer', 'exists:product_images,id'],
            'primary_image_id' => ['nullable', 'string'],
        ]);

        $product = Product::findOrFail($request->input('product_id'));
        $deletedCount = 0;
        $uploadedCount = 0;

        DB::transaction(function () use ($request, $product, &$deletedCount, &$uploadedCount) {
            // 1. Delete marked images
            $deleteIds = $request->input('delete_image_ids', []);
            if (! empty($deleteIds)) {
                $imagesToDelete = ProductImage::where('product_id', $product->id)
                    ->whereIn('id', $deleteIds)
                    ->get();

                foreach ($imagesToDelete as $img) {
                    if ($img->image_path && Storage::disk('public')->exists($img->image_path)) {
                        Storage::disk('public')->delete($img->image_path);
                    }
                    $img->delete();
                    $deletedCount++;
                }
            }

            // 2. Upload new images
            $newlyCreatedImages = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('products', 'public');
                    $newImg = $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => false,
                        'sort_order' => $product->images()->count() + $index,
                    ]);
                    $newlyCreatedImages[$index] = $newImg;
                    $uploadedCount++;
                }
            }

            // 3. Handle primary cover selection
            $primarySelection = $request->input('primary_image_id');

            // Reset all remaining images to not primary first
            $product->images()->update(['is_primary' => false]);

            if ($primarySelection && str_starts_with($primarySelection, 'new_')) {
                $newIndex = (int) str_replace('new_', '', $primarySelection);
                if (isset($newlyCreatedImages[$newIndex])) {
                    $newlyCreatedImages[$newIndex]->update(['is_primary' => true]);
                }
            } elseif ($primarySelection && is_numeric($primarySelection)) {
                $targetImg = $product->images()->find($primarySelection);
                if ($targetImg) {
                    $targetImg->update(['is_primary' => true]);
                }
            }

            // Ensure at least one image is primary if images exist
            $hasPrimary = $product->images()->where('is_primary', true)->exists();
            if (! $hasPrimary) {
                $firstRemaining = $product->images()->first();
                if ($firstRemaining) {
                    $firstRemaining->update(['is_primary' => true]);
                }
            }
        });

        $messageParts = [];
        if ($uploadedCount > 0) {
            $messageParts[] = "{$uploadedCount} new image(s) uploaded";
        }
        if ($deletedCount > 0) {
            $messageParts[] = "{$deletedCount} image(s) removed";
        }
        if (empty($messageParts)) {
            $messageParts[] = 'Cover photo updated';
        }

        $summary = implode(', ', $messageParts);

        return redirect()->route('admin.products.images.index', ['product_id' => $product->id])
            ->with('success', "Changes saved: {$summary} for '{$product->name}'.");
    }

    /**
     * Store newly uploaded product images directly.
     */
    public function store(Request $request): RedirectResponse
    {
        return $this->saveGallery($request);
    }

    /**
     * Set the specified image as the primary cover photo for its product.
     */
    public function setPrimary(ProductImage $image): RedirectResponse
    {
        // Reset all images of this product to not primary
        ProductImage::where('product_id', $image->product_id)->update(['is_primary' => false]);

        // Set this image as primary
        $image->update(['is_primary' => true]);

        return back()->with('success', "Image set as primary cover photo for {$image->product->name}.");
    }

    /**
     * Remove the specified product image from storage.
     */
    public function destroy(ProductImage $image): RedirectResponse
    {
        $product = $image->product;
        $wasPrimary = $image->is_primary;

        if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        // If the deleted image was primary, assign primary to the next available image
        if ($wasPrimary && $product) {
            $nextImage = $product->images()->first();
            if ($nextImage) {
                $nextImage->update(['is_primary' => true]);
            }
        }

        return back()->with('success', 'Product image removed successfully.');
    }
}
