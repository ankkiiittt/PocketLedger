<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories grouped by type.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $showArchived = $request->boolean('show_archived', false);

        $expenseQuery = $user->categories()
            ->withCount('transactions')
            ->expense()
            ->orderBy('name');

        $incomeQuery = $user->categories()
            ->withCount('transactions')
            ->income()
            ->orderBy('name');

        if (! $showArchived) {
            $expenseQuery->active();
            $incomeQuery->active();
        }

        return view('categories.index', [
            'expenseCategories' => $expenseQuery->get(),
            'incomeCategories' => $incomeQuery->get(),
            'showArchived' => $showArchived,
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        return view('categories.create', [
            'presetColors' => Category::PRESET_COLORS,
            'types' => CategoryType::cases(),
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->categories()->create([
            'name' => trim($request->string('name')->value()),
            'type' => $request->enum('type', CategoryType::class),
            'color' => $request->string('color')->value(),
            'is_archived' => false,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Request $request, Category $category): View
    {
        $this->authorize('update', $category);

        return view('categories.edit', [
            'category' => $category,
            'isTypeLocked' => $category->isTypeLocked(),
            'presetColors' => Category::PRESET_COLORS,
            'types' => CategoryType::cases(),
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $data = [
            'name' => trim($request->string('name')->value()),
            'color' => $request->string('color')->value(),
        ];

        if (! $category->isTypeLocked()) {
            $data['type'] = $request->enum('type', CategoryType::class);
        }

        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->isUsed()) {
            return redirect()->route('categories.index')
                ->with('error', 'This category has transactions or recurring rules. Archive it instead.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }

    /**
     * Archive the specified category.
     */
    public function archive(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('archive', $category);

        $category->update(['is_archived' => true]);

        return redirect()->route('categories.index')->with('success', 'Category archived.');
    }

    /**
     * Restore the specified category.
     */
    public function restore(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('restore', $category);

        $category->update(['is_archived' => false]);

        return redirect()->route('categories.index')->with('success', 'Category restored.');
    }
}
