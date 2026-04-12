@php
    $routeName = request()->route()->getName();

    $menuItems = [
        [
            'route' => 'admin.dashboard',
            'icon'  => 'bx-home-circle',
            'label' => 'Dashboard',
        ],

        [
            'route' => 'admin.users.index',
            'icon'  => 'bx-user',
            'label' => 'Users',
        ],

        [
            'route' => 'admin.stitch.index',
            'icon'  => 'bx-user',
            'label' => 'Stitchs',
        ],
        [
            'route' => 'admin.categories.index',
            'icon'  => 'bx-ruler',
            'label' => 'Measurement',
        ],
        [
            'route' => 'admin.orders.index',
            'icon'  => 'bx-cart',
            'label' => 'Orders',
        ],
    ];
@endphp

<aside class="layout-menu menu-vertical menu bg-menu-theme">

    {{-- ================= LOGO ================= --}}
    <div class="app-brand demo">
        <a href="{{ route('admin.dashboard') }}" class="app-brand-link">

            <span class="app-brand-logo demo">
            </span>

            <span class="app-brand-text fw-bold ms-2">
                {{ config('app.name') }}
            </span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link ms-auto d-xl-none">
            <i class="bx bx-chevron-left bx-sm"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    {{-- ================= MENU ================= --}}
    <ul class="menu-inner py-1">

        {{-- ===== STATIC MENU ===== --}}
        @foreach($menuItems as $item)
            <li class="menu-item {{ $routeName == $item['route'] ? 'active' : '' }}">
                <a href="{{ route($item['route']) }}" class="menu-link">
                    <i class="menu-icon tf-icons bx {{ $item['icon'] }}"></i>
                    <div>{{ $item['label'] }}</div>
                </a>
            </li>
        @endforeach

    </ul>
</aside>