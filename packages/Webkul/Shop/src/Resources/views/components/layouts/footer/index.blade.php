{!! view_render_event('bagisto.shop.layout.footer.before') !!}

@if (request()->routeIs('shop.customer.session.create', 'shop.customers.register.index'))
    {!! view_render_event('bagisto.shop.layout.footer.after') !!}

    @php(return)
@endif

<footer class="mt-9 text-white max-sm:mt-10" style="background:#123C8D; color:#ffffff;">
    <div class="mx-auto grid max-w-[1520px] gap-10 px-[60px] py-12 md:grid-cols-2 xl:grid-cols-[1.35fr_1fr_1fr_1fr] max-md:px-8 max-sm:px-4">
        <div class="max-w-[340px]">
            <a href="{{ route('shop.home.index') }}" class="flex items-center gap-3">
                <span class="flex h-16 w-16 items-center justify-center rounded-full border border-white/35 bg-white p-1.5 shadow-sm">
                    <img
                        src="{{ asset('logodjouf.webp') }}"
                        alt="Djouf Inter"
                        class="h-full w-full rounded-full object-contain"
                    >
                </span>

                <span class="text-2xl font-semibold tracking-wide text-white">Djouf Inter</span>
            </a>

            <p class="mt-5 text-sm leading-7 text-white/90">
                Votre boutique premium de boissons: whiskies, vins, champagnes, aperitifs et spiritueux. Qualite, authenticite et livraison rapide.
            </p>

            <div class="mt-6 flex flex-wrap gap-3 text-sm text-white/85">
                <span class="rounded-full border border-white/20 px-3 py-1">Whiskies</span>
                <span class="rounded-full border border-white/20 px-3 py-1">Vins</span>
                <span class="rounded-full border border-white/20 px-3 py-1">Champagnes</span>
                <span class="rounded-full border border-white/20 px-3 py-1">Rhums</span>
            </div>
        </div>

        <div class="md:justify-self-start xl:justify-self-center">
            <h4 class="text-base font-semibold uppercase tracking-[0.2em] text-white">Navigation</h4>

            <ul class="mt-5 grid gap-3 text-sm text-white/90">
                <li><a href="{{ route('shop.home.index') }}" class="hover:text-white">Accueil</a></li>
                <li><a href="{{ route('shop.home.index') }}#featured-products" class="hover:text-white">Nos boissons</a></li>
                <li><a href="{{ route('shop.checkout.cart.index') }}" class="hover:text-white">Panier</a></li>
                <li><a href="{{ route('shop.customers.account.index') }}" class="hover:text-white">Mon compte</a></li>
                <li><a href="{{ route('shop.home.index') }}#contact" class="hover:text-white">Contact</a></li>
            </ul>
        </div>

        <div class="md:justify-self-start xl:justify-self-center">
            <h4 class="text-base font-semibold uppercase tracking-[0.2em] text-white">Rayons boissons</h4>

            <ul class="mt-5 grid gap-3 text-sm text-white/90">
                <li><a href="{{ route('shop.search.index', ['query' => 'whisky']) }}" class="hover:text-white">Whiskies</a></li>
                <li><a href="{{ route('shop.search.index', ['query' => 'vin']) }}" class="hover:text-white">Vins</a></li>
                <li><a href="{{ route('shop.search.index', ['query' => 'champagne']) }}" class="hover:text-white">Champagnes</a></li>
                <li><a href="{{ route('shop.search.index', ['query' => 'rhum']) }}" class="hover:text-white">Rhums</a></li>
                <li><a href="{{ route('shop.search.index', ['query' => 'aperitif']) }}" class="hover:text-white">Aperitifs</a></li>
            </ul>
        </div>

        <div id="contact" class="md:justify-self-start xl:justify-self-end xl:min-w-[260px]">
            <h4 class="text-base font-semibold uppercase tracking-[0.2em] text-white">Contact</h4>

            <div class="mt-5 grid gap-4 text-sm text-white/90">
                <div class="rounded-2xl border border-white/15 bg-white/5 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-white/70">Horaires</p>
                    <p class="mt-2 leading-6">Lundi a dimanche<br>7h a 23h</p>
                </div>

                <div class="rounded-2xl border border-white/15 bg-white/5 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-white/70">Contact</p>
                    <p class="mt-2 break-all">
                        <a href="https://djoufinter.com" class="hover:text-white">djoufinter.com</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="border-t border-white/20 px-[60px] py-4 max-md:px-8 max-sm:px-4" style="background:#0F347D; color:#ffffff;">
        <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-white/90 max-md:flex-col max-md:items-start">
            <p>Powered by OMD. Tous droit reserver.</p>

            <div class="flex flex-wrap items-center gap-3 text-white/90">
                <span>Selection premium de boissons</span>
                <span>|</span>
                <span>Vins &amp; Champagnes</span>
                <span>|</span>
                <span>Whiskies &amp; Rhums</span>
                <span>|</span>
                <span>Spiritueux &amp; Aperitifs</span>
            </div>
        </div>
    </div>
</footer>

{!! view_render_event('bagisto.shop.layout.footer.after') !!}
