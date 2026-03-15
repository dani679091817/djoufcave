{!! view_render_event('bagisto.shop.layout.header.before') !!}

@if(core()->getCurrentChannel()->locales()->count() > 1 || core()->getCurrentChannel()->currencies()->count() > 1)
    <div class="max-lg:hidden">
        <x-shop::layouts.header.desktop.top />
    </div>
@endif

@php
    $showCompare = (bool) core()->getConfigData('catalog.products.settings.compare_option');
    $showImageSearch = (bool) core()->getConfigData('catalog.products.settings.image_search');
@endphp

<header class="sticky top-0 z-20 shadow-lg">
    <div class="border-b border-white/20 bg-gradient-to-r from-[#1c3d8c] to-[#274fa3] text-white">
        <div class="mx-auto max-w-[1520px] px-4 py-4 md:px-8 lg:px-12">
            <div class="hidden items-center gap-6 lg:flex">
                <div class="flex min-w-[270px] items-center gap-3">
                    <a
                        href="{{ route('shop.home.index') }}"
                        class="inline-flex items-center gap-3"
                        aria-label="Djouf Inter"
                    >
                        <span class="grid h-12 w-12 place-items-center rounded-full border border-white/70 bg-white/15 p-1.5">
                            <img
                                src="{{ asset('logodjouf.webp') }}"
                                alt="Djouf Inter"
                                class="h-full w-full rounded-full object-cover"
                            >
                        </span>

                        <span class="text-2xl font-semibold tracking-wide">Djouf Inter</span>
                    </a>

                    <a
                        href="{{ route('shop.home.index') }}"
                        class="ml-4 text-sm font-semibold uppercase tracking-[0.25em] text-white/95 transition hover:text-[#e8f1ff]"
                    >
                        Hommes
                    </a>
                </div>

                <div class="flex-1">
                    <form
                        action="{{ route('shop.search.index') }}"
                        role="search"
                        class="relative"
                    >
                        <label
                            for="main-nav-search"
                            class="sr-only"
                        >
                            Rechercher
                        </label>

                        <span class="icon-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xl text-[#3959A9]"></span>

                        <input
                            id="main-nav-search"
                            type="text"
                            name="query"
                            value="{{ request('query') }}"
                            class="h-14 w-full rounded-full border border-white/30 bg-white px-12 pr-20 text-base text-[#1a2a56] outline-none transition focus:border-white focus:ring-2 focus:ring-white/40"
                            minlength="{{ core()->getConfigData('catalog.products.search.min_query_length') }}"
                            maxlength="{{ core()->getConfigData('catalog.products.search.max_query_length') }}"
                            placeholder="Recherchez des produits ici"
                            aria-label="Recherchez des produits ici"
                            pattern="[^\\]+"
                            required
                        >

                        <div class="absolute right-3 top-1/2 flex -translate-y-1/2 items-center gap-1.5">
                            @if ($showImageSearch)
                                @include('shop::search.images.index')
                            @endif

                            <button
                                type="submit"
                                class="grid h-9 w-9 place-items-center rounded-full bg-[#1f4598] text-white transition hover:bg-[#183b84]"
                                aria-label="Lancer la recherche"
                            >
                                <span class="icon-camera text-lg"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="flex items-center gap-5 pl-2">
                    @if($showCompare)
                        <a
                            href="{{ route('shop.compare.index') }}"
                            class="grid h-10 w-10 place-items-center rounded-full border border-white/30 text-white transition hover:bg-white/10"
                            aria-label="Comparer"
                        >
                            <span class="icon-compare text-xl"></span>
                        </a>
                    @endif

                    @if(core()->getConfigData('sales.checkout.shopping_cart.cart_page'))
                        <div class="text-white [&_.icon-cart]:text-white [&_.icon-cart]:text-xl [&_.icon-cart:hover]:text-white [&_.mini-cart-button]:grid [&_.mini-cart-button]:h-10 [&_.mini-cart-button]:w-10 [&_.mini-cart-button]:place-items-center [&_.mini-cart-button]:rounded-full [&_.mini-cart-button]:border [&_.mini-cart-button]:border-white/30 [&_.mini-cart-button:hover]:bg-white/10">
                            @include('shop::checkout.cart.mini-cart')
                        </div>
                    @endif

                    <x-shop::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'right' : 'left' }}">
                        <x-slot:toggle>
                            <span
                                class="icon-users grid h-10 w-10 cursor-pointer place-items-center rounded-full border border-white/30 text-xl text-white transition hover:bg-white/10"
                                role="button"
                                aria-label="Mon compte"
                                tabindex="0"
                            ></span>
                        </x-slot>

                        @guest('customer')
                            <x-slot:content>
                                <div class="grid gap-2.5">
                                    <p class="text-lg font-semibold text-[#1f2d55]">
                                        @lang('shop::app.components.layouts.header.desktop.bottom.welcome-guest')
                                    </p>

                                    <p class="text-sm text-zinc-600">
                                        @lang('shop::app.components.layouts.header.desktop.bottom.dropdown-text')
                                    </p>
                                </div>

                                <p class="mt-3 w-full border border-zinc-200"></p>

                                <div class="mt-5 flex gap-3">
                                    <a
                                        href="{{ route('shop.customer.session.create') }}"
                                        class="rounded-xl bg-[#1f4598] px-5 py-2 text-sm font-semibold text-white hover:bg-[#183b84]"
                                    >
                                        @lang('shop::app.components.layouts.header.desktop.bottom.sign-in')
                                    </a>

                                    <a
                                        href="{{ route('shop.customers.register.index') }}"
                                        class="rounded-xl border border-[#1f4598] px-5 py-2 text-sm font-semibold text-[#1f4598] hover:bg-[#eef3ff]"
                                    >
                                        @lang('shop::app.components.layouts.header.desktop.bottom.sign-up')
                                    </a>
                                </div>
                            </x-slot>
                        @endguest

                        @auth('customer')
                            <x-slot:content class="!p-0">
                                <div class="grid gap-2.5 p-5 pb-0">
                                    <p class="text-lg font-semibold text-[#1f2d55]" v-pre>
                                        @lang('shop::app.components.layouts.header.desktop.bottom.welcome') {{ auth()->guard('customer')->user()->first_name }}
                                    </p>

                                    <p class="text-sm text-zinc-600">
                                        @lang('shop::app.components.layouts.header.desktop.bottom.dropdown-text')
                                    </p>
                                </div>

                                <p class="mt-3 w-full border border-zinc-200"></p>

                                <div class="mt-2 grid gap-1 pb-2.5">
                                    <a class="px-5 py-2 text-sm hover:bg-gray-100" href="{{ route('shop.customers.account.profile.index') }}">@lang('shop::app.components.layouts.header.desktop.bottom.profile')</a>
                                    <a class="px-5 py-2 text-sm hover:bg-gray-100" href="{{ route('shop.customers.account.orders.index') }}">@lang('shop::app.components.layouts.header.desktop.bottom.orders')</a>

                                    <x-shop::form method="DELETE" action="{{ route('shop.customer.session.destroy') }}" id="headerCustomerLogout" />
                                    <a
                                        class="px-5 py-2 text-sm hover:bg-gray-100"
                                        href="{{ route('shop.customer.session.destroy') }}"
                                        onclick="event.preventDefault(); document.getElementById('headerCustomerLogout').submit();"
                                    >
                                        @lang('shop::app.components.layouts.header.desktop.bottom.logout')
                                    </a>
                                </div>
                            </x-slot>
                        @endauth
                    </x-shop::dropdown>
                </div>
            </div>

            <div class="space-y-4 lg:hidden">
                <div class="flex items-center justify-between">
                    <a
                        href="{{ route('shop.home.index') }}"
                        class="inline-flex items-center gap-2"
                        aria-label="Djouf Inter"
                    >
                        <span class="grid h-10 w-10 place-items-center rounded-full border border-white/70 bg-white/15 p-1.5">
                            <img
                                src="{{ asset('logodjouf.webp') }}"
                                alt="Djouf Inter"
                                class="h-full w-full rounded-full object-cover"
                            >
                        </span>

                        <span class="text-lg font-semibold">Djouf Inter</span>
                    </a>

                    <div class="flex items-center gap-2">
                        @if($showCompare)
                            <a href="{{ route('shop.compare.index') }}" class="grid h-9 w-9 place-items-center rounded-full border border-white/30" aria-label="Comparer">
                                <span class="icon-compare text-lg"></span>
                            </a>
                        @endif

                        @if(core()->getConfigData('sales.checkout.shopping_cart.cart_page'))
                            <div class="text-white [&_.icon-cart]:text-white [&_.mini-cart-button]:grid [&_.mini-cart-button]:h-9 [&_.mini-cart-button]:w-9 [&_.mini-cart-button]:place-items-center [&_.mini-cart-button]:rounded-full [&_.mini-cart-button]:border [&_.mini-cart-button]:border-white/30">
                                @include('shop::checkout.cart.mini-cart')
                            </div>
                        @endif

                        @guest('customer')
                            <a href="{{ route('shop.customer.session.create') }}" class="grid h-9 w-9 place-items-center rounded-full border border-white/30" aria-label="Mon compte">
                                <span class="icon-users text-lg"></span>
                            </a>
                        @endguest

                        @auth('customer')
                            <a href="{{ route('shop.customers.account.index') }}" class="grid h-9 w-9 place-items-center rounded-full border border-white/30" aria-label="Mon compte">
                                <span class="icon-users text-lg"></span>
                            </a>
                        @endauth
                    </div>
                </div>

                <a
                    href="{{ route('shop.home.index') }}"
                    class="inline-block text-xs font-semibold uppercase tracking-[0.2em] text-white/95"
                >
                    Hommes
                </a>

                <form
                    action="{{ route('shop.search.index') }}"
                    role="search"
                    class="relative"
                >
                    <label for="mobile-nav-search" class="sr-only">Rechercher</label>

                    <span class="icon-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#3959A9]"></span>

                    <input
                        id="mobile-nav-search"
                        type="text"
                        name="query"
                        value="{{ request('query') }}"
                        class="h-12 w-full rounded-full border border-white/30 bg-white px-11 pr-20 text-sm text-[#1a2a56] outline-none transition focus:border-white focus:ring-2 focus:ring-white/40"
                        minlength="{{ core()->getConfigData('catalog.products.search.min_query_length') }}"
                        maxlength="{{ core()->getConfigData('catalog.products.search.max_query_length') }}"
                        placeholder="Recherchez des produits ici"
                        aria-label="Recherchez des produits ici"
                        pattern="[^\\]+"
                        required
                    >

                    <div class="absolute right-3 top-1/2 flex -translate-y-1/2 items-center gap-1.5">
                        @if ($showImageSearch)
                            @include('shop::search.images.index')
                        @endif

                        <button type="submit" class="grid h-8 w-8 place-items-center rounded-full bg-[#1f4598] text-white" aria-label="Lancer la recherche">
                            <span class="icon-camera text-base"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="border-b border-[#d9e3ff]/50 bg-[#f6f9ff]">
        <div class="mx-auto grid max-w-[1520px] gap-3 px-4 py-4 md:grid-cols-2 md:px-8 lg:grid-cols-4 lg:gap-4 lg:px-12">
            <div class="flex items-start gap-3 rounded-2xl border border-[#d6e2ff] bg-white/70 px-3 py-3 text-[#1a316d]">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="mt-0.5 h-5 w-5 flex-none stroke-current" stroke-width="1.8"><path d="M3 7h12l3 4v6H7a2 2 0 1 1-4 0V7Zm4 10a2 2 0 1 0 4 0m7 0a2 2 0 1 0 4 0m0-6h-3V9"/></svg>
                <p class="text-xs font-medium leading-5">Profitez de la livraison gratuite sur toutes les commandes</p>
            </div>

            <div class="flex items-start gap-3 rounded-2xl border border-[#d6e2ff] bg-white/70 px-3 py-3 text-[#1a316d]">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="mt-0.5 h-5 w-5 flex-none stroke-current" stroke-width="1.8"><path d="M16 3h5v5M8 21H3v-5m0-8V3h5m11 18v-5h-5"/><path d="M8 8h8v8H8z"/></svg>
                <p class="text-xs font-medium leading-5">Remplacement facile de produit disponible</p>
            </div>

            <div class="flex items-start gap-3 rounded-2xl border border-[#d6e2ff] bg-white/70 px-3 py-3 text-[#1a316d]">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="mt-0.5 h-5 w-5 flex-none stroke-current" stroke-width="1.8"><rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 10h19"/></svg>
                <p class="text-xs font-medium leading-5">EMI sans frais disponible sur toutes les principales cartes de credit</p>
            </div>

            <div class="flex items-start gap-3 rounded-2xl border border-[#d6e2ff] bg-white/70 px-3 py-3 text-[#1a316d]">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="mt-0.5 h-5 w-5 flex-none stroke-current" stroke-width="1.8"><path d="M12 21c4.97 0 9-3.58 9-8s-4.03-8-9-8-9 3.58-9 8c0 2.17.98 4.13 2.57 5.56L5 21l3.23-1.38A10.18 10.18 0 0 0 12 21Z"/><path d="M9 11h6M9 14h4"/></svg>
                <p class="text-xs font-medium leading-5">Support dedie 24/7 via chat et e-mail</p>
            </div>
        </div>
    </div>
</header>

{!! view_render_event('bagisto.shop.layout.header.after') !!}
