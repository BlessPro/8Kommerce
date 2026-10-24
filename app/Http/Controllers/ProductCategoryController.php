<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = ProductCategory::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get()
            ->map(function (ProductCategory $category) use ($request) {
                $category->product_count = Product::query()
                    ->where('user_id', $request->user()->id)
                    ->where('category', $category->name)
                    ->count();

                return $category;
            });

        return view('dashboard.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'categories' => ['required', 'string', 'max:1000'],
        ]);

        $names = collect(explode(',', $validated['categories']))
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique(fn (string $name) => ProductCategory::normalize($name))
            ->take(25);

        if ($names->isEmpty()) {
            return back()->withErrors(['categories' => 'Enter at least one category.'])->withInput();
        }

        $names->each(fn (string $name) => ProductCategory::remember($request->user()->id, $name));

        return back()->with('status', $names->count().' '.str('category')->plural($names->count()).' added.');
    }

    public function update(Request $request, ProductCategory $category)
    {
        abort_unless((int) $category->user_id === (int) $request->user()->id, 403);

        $validated = $request->validate(['name' => ['required', 'string', 'max:80']]);
        $name = trim($validated['name']);
        if (ProductCategory::query()->where('user_id', $request->user()->id)
            ->where('normalized_name', ProductCategory::normalize($name))->whereKeyNot($category->id)->exists()) {
            return back()->withErrors(['name' => 'That category already exists.'])->withInput();
        }
        $oldName = $category->name;

        DB::transaction(function () use ($category, $name, $oldName, $request) {
            $category->update(['name' => $name, 'normalized_name' => ProductCategory::normalize($name)]);
            Product::query()->where('user_id', $request->user()->id)->where('category', $oldName)->update(['category' => $name]);
        });

        return back()->with('status', 'Category updated everywhere.');
    }

    public function destroy(Request $request, ProductCategory $category)
    {
        abort_unless((int) $category->user_id === (int) $request->user()->id, 403);

        DB::transaction(function () use ($category, $request) {
            Product::query()->where('user_id', $request->user()->id)->where('category', $category->name)->update(['category' => null]);
            $category->delete();
        });

        return back()->with('status', 'Category deleted. Its products are now uncategorised.');
    }
}
