<x-app-layout title="Categories">
    <div class="categories-toolbar">
        <div>
            <h1 class="page-title">Categories</h1>
            <p class="section-subtitle">Organise your income and expenses into clear categories.</p>
        </div>

        <div style="display: flex; align-items: center; gap: var(--space-4); flex-wrap: wrap;">
            <a href="{{ route('categories.index', ['show_archived' => $showArchived ? 0 : 1]) }}"
               class="btn btn-secondary">
                {{ $showArchived ? 'Hide archived' : 'Show archived' }}
            </a>
            <a href="{{ route('categories.create') }}" class="btn btn-primary">
                <x-icon name="plus" size="sm" />
                <span>Add category</span>
            </a>
        </div>
    </div>

    <div class="categories-grid">
        {{-- Expense Categories --}}
        <section aria-labelledby="heading-expense-categories">
            <h2 id="heading-expense-categories" class="category-section-title">Expense Categories</h2>

            @if ($expenseCategories->isEmpty())
                <div class="card" style="padding: var(--space-6); text-align: center;">
                    <p class="text-muted">No expense categories to show. Add one above.</p>
                </div>
            @else
                <div class="card" style="padding: 0; overflow: hidden;">
                    <div role="list">
                        @foreach ($expenseCategories as $category)
                            <div class="category-row-item" role="listitem">
                                <div class="category-info">
                                    <span class="category-color-dot" style="background-color: {{ $category->color }};" aria-hidden="true"></span>
                                    <div>
                                        <span class="category-name">{{ $category->name }}</span>
                                        <div class="category-meta" style="margin-top: 2px;">
                                            <span class="text-muted text-sm">{{ $category->transactions_count }} {{ \Illuminate\Support\Str::plural('transaction', $category->transactions_count) }}</span>
                                            @if ($category->is_archived)
                                                <x-badge variant="neutral" text="Archived" />
                                            @else
                                                <x-badge variant="neutral" text="Active" />
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="category-actions">
                                    <a href="{{ route('categories.edit', $category) }}" class="btn btn-secondary btn-sm">Edit</a>

                                    @if ($category->is_archived)
                                        <form method="POST" action="{{ route('categories.restore', $category) }}" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-secondary btn-sm">Restore</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('categories.archive', $category) }}" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-secondary btn-sm">Archive</button>
                                        </form>
                                    @endif

                                    @if (! $category->isUsed())
                                        <form method="POST" action="{{ route('categories.destroy', $category) }}" style="display:inline;" data-confirm="Are you sure you want to delete category '{{ $category->name }}'?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        {{-- Income Categories --}}
        <section aria-labelledby="heading-income-categories">
            <h2 id="heading-income-categories" class="category-section-title">Income Categories</h2>

            @if ($incomeCategories->isEmpty())
                <div class="card" style="padding: var(--space-6); text-align: center;">
                    <p class="text-muted">No income categories to show. Add one above.</p>
                </div>
            @else
                <div class="card" style="padding: 0; overflow: hidden;">
                    <div role="list">
                        @foreach ($incomeCategories as $category)
                            <div class="category-row-item" role="listitem">
                                <div class="category-info">
                                    <span class="category-color-dot" style="background-color: {{ $category->color }};" aria-hidden="true"></span>
                                    <div>
                                        <span class="category-name">{{ $category->name }}</span>
                                        <div class="category-meta" style="margin-top: 2px;">
                                            <span class="text-muted text-sm">{{ $category->transactions_count }} {{ \Illuminate\Support\Str::plural('transaction', $category->transactions_count) }}</span>
                                            @if ($category->is_archived)
                                                <x-badge variant="neutral" text="Archived" />
                                            @else
                                                <x-badge variant="neutral" text="Active" />
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="category-actions">
                                    <a href="{{ route('categories.edit', $category) }}" class="btn btn-secondary btn-sm">Edit</a>

                                    @if ($category->is_archived)
                                        <form method="POST" action="{{ route('categories.restore', $category) }}" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-secondary btn-sm">Restore</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('categories.archive', $category) }}" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-secondary btn-sm">Archive</button>
                                        </form>
                                    @endif

                                    @if (! $category->isUsed())
                                        <form method="POST" action="{{ route('categories.destroy', $category) }}" style="display:inline;" data-confirm="Are you sure you want to delete category '{{ $category->name }}'?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
