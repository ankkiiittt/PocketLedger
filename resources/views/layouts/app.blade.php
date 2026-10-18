<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? "{$title} - PocketLedger" : "PocketLedger" }}</title>
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <div class="app-shell">
        <!-- Mobile Top Bar -->
        <header class="mobile-top-bar" role="banner">
            <button
                type="button"
                class="btn btn-ghost"
                data-drawer-toggle
                aria-expanded="false"
                aria-controls="navigation-drawer"
                aria-label="Open navigation menu"
            >
                <x-icon name="menu" size="24" />
            </button>

            <a href="{{ route('dashboard') }}" class="mobile-brand">
                PocketLedger
            </a>

            <x-button variant="primary" size="sm" :href="route('transactions.create')" aria-label="Add transaction">
                <x-icon name="plus" size="16" />
                <span class="sr-only">Add transaction</span>
            </x-button>
        </header>

        <!-- Mobile Drawer Backdrop -->
        <div class="drawer-backdrop" data-drawer-backdrop aria-hidden="true"></div>

        <!-- Navigation Drawer (Mobile) -->
        <aside id="navigation-drawer" class="nav-drawer" data-drawer aria-label="Mobile Navigation">
            <div class="sidebar-header">
                <a href="{{ route('dashboard') }}" class="brand-title">
                    <span>PocketLedger</span>
                </a>
                <button type="button" class="btn btn-ghost btn-sm" data-drawer-close aria-label="Close navigation menu">
                    <x-icon name="close" size="20" />
                </button>
            </div>

            <nav class="sidebar-nav" aria-label="Main navigation">
                <a href="{{ route('dashboard') }}" class="nav-item" @if(request()->routeIs('dashboard*')) aria-current="page" @endif>
                    <x-icon name="dashboard" size="20" />
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('transactions.index') }}" class="nav-item" @if(request()->routeIs('transactions*')) aria-current="page" @endif>
                    <x-icon name="transactions" size="20" />
                    <span>Transactions</span>
                </a>
                <a href="{{ route('budgets.index') }}" class="nav-item" @if(request()->routeIs('budgets*')) aria-current="page" @endif>
                    <x-icon name="budgets" size="20" />
                    <span>Budgets</span>
                </a>
                <a href="{{ route('recurring.index') }}" class="nav-item" @if(request()->routeIs('recurring*')) aria-current="page" @endif>
                    <x-icon name="recurring" size="20" />
                    <span>Recurring</span>
                </a>
                <a href="{{ route('reports.index') }}" class="nav-item" @if(request()->routeIs('reports*')) aria-current="page" @endif>
                    <x-icon name="reports" size="20" />
                    <span>Reports</span>
                </a>
                <a href="{{ route('categories.index') }}" class="nav-item" @if(request()->routeIs('categories*')) aria-current="page" @endif>
                    <x-icon name="categories" size="20" />
                    <span>Categories</span>
                </a>
                <a href="{{ route('settings.edit') }}" class="nav-item" @if(request()->routeIs('settings*')) aria-current="page" @endif>
                    <x-icon name="settings" size="20" />
                    <span>Settings</span>
                </a>
            </nav>

            <div class="sidebar-user">
                @auth
                    <div class="user-info">
                        <span class="user-name">{{ auth()->user()->name }}</span>
                        <span class="user-email">{{ auth()->user()->email }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm btn-block" style="justify-content: flex-start;">
                            <x-icon name="logout" size="16" />
                            <span>Sign out</span>
                        </button>
                    </form>
                @endauth
            </div>
        </aside>

        <!-- Desktop Sidebar -->
        <aside class="desktop-sidebar" aria-label="Sidebar navigation">
            <div>
                <div class="sidebar-header">
                    <a href="{{ route('dashboard') }}" class="brand-title">
                        <span>PocketLedger</span>
                    </a>
                </div>

                <nav class="sidebar-nav" aria-label="Desktop main navigation">
                    <a href="{{ route('dashboard') }}" class="nav-item" @if(request()->routeIs('dashboard*')) aria-current="page" @endif>
                        <x-icon name="dashboard" size="20" />
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('transactions.index') }}" class="nav-item" @if(request()->routeIs('transactions*')) aria-current="page" @endif>
                        <x-icon name="transactions" size="20" />
                        <span>Transactions</span>
                    </a>
                    <a href="{{ route('budgets.index') }}" class="nav-item" @if(request()->routeIs('budgets*')) aria-current="page" @endif>
                        <x-icon name="budgets" size="20" />
                        <span>Budgets</span>
                    </a>
                    <a href="{{ route('recurring.index') }}" class="nav-item" @if(request()->routeIs('recurring*')) aria-current="page" @endif>
                        <x-icon name="recurring" size="20" />
                        <span>Recurring</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="nav-item" @if(request()->routeIs('reports*')) aria-current="page" @endif>
                        <x-icon name="reports" size="20" />
                        <span>Reports</span>
                    </a>
                    <a href="{{ route('categories.index') }}" class="nav-item" @if(request()->routeIs('categories*')) aria-current="page" @endif>
                        <x-icon name="categories" size="20" />
                        <span>Categories</span>
                    </a>
                    <a href="{{ route('settings.edit') }}" class="nav-item" @if(request()->routeIs('settings*')) aria-current="page" @endif>
                        <x-icon name="settings" size="20" />
                        <span>Settings</span>
                    </a>
                </nav>
            </div>

            <div class="sidebar-user">
                @auth
                    <div class="user-info">
                        <span class="user-name">{{ auth()->user()->name }}</span>
                        <span class="user-email">{{ auth()->user()->email }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm btn-block" style="justify-content: flex-start;">
                            <x-icon name="logout" size="16" />
                            <span>Sign out</span>
                        </button>
                    </form>
                @endauth
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="app-main-wrap">
            <header class="app-header">
                <div class="app-header-inner">
                    <h1 class="text-h2">{{ $header ?? $title ?? 'PocketLedger' }}</h1>
                    <div>
                        {{ $headerActions ?? '' }}
                        @if (!isset($hideAddTransaction) || !$hideAddTransaction)
                            <x-button variant="primary" :href="route('transactions.create')">
                                <x-icon name="plus" size="18" />
                                <span>Add transaction</span>
                            </x-button>
                        @endif
                    </div>
                </div>
            </header>

            <!-- Flash messages -->
            <div class="flash-container">
                @if (session('success'))
                    <x-flash type="success" :message="session('success')" />
                @endif
                @if (session('error'))
                    <x-flash type="error" :message="session('error')" />
                @endif
                @if (session('warning'))
                    <x-flash type="warning" :message="session('warning')" />
                @endif
                @if (session('status'))
                    <x-flash type="info" :message="session('status')" />
                @endif
                @if (session('info'))
                    <x-flash type="info" :message="session('info')" />
                @endif
            </div>

            <!-- Page Slot -->
            <main id="main-content" class="app-content">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Reusable Confirmation Dialog -->
    <dialog id="confirm-modal" class="confirm-modal" aria-labelledby="confirm-modal-title" aria-describedby="confirm-modal-message">
        <div class="modal-header">
            <h2 id="confirm-modal-title" class="modal-title">Please confirm</h2>
        </div>
        <p id="confirm-modal-message" class="modal-body">
            Are you sure you want to proceed with this action?
        </p>
        <div class="modal-actions">
            <x-button variant="secondary" id="confirm-modal-cancel">
                Cancel
            </x-button>
            <x-button variant="danger" id="confirm-modal-confirm">
                Confirm
            </x-button>
        </div>
    </dialog>
</body>
</html>
