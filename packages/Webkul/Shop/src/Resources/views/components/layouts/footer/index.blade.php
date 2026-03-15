{!! view_render_event('bagisto.shop.layout.footer.before') !!}

<footer class="mt-9 text-white max-sm:mt-10" style="background:#123C8D; color:#ffffff;">
    <div class="mx-auto grid max-w-[1520px] gap-10 px-[60px] py-12 lg:grid-cols-[1.2fr_1fr_1fr_1fr] max-md:px-8 max-sm:px-4">
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
        </div>

        <div>
            <h4 class="text-base font-semibold uppercase tracking-[0.2em] text-white">Navigation</h4>

            <ul class="mt-5 grid gap-3 text-sm text-white/90">
                <li><a href="{{ route('shop.home.index') }}" class="hover:text-white">Accueil</a></li>
                <li><a href="{{ route('shop.home.index') }}#featured-products" class="hover:text-white">Nos boissons</a></li>
                <li><a href="{{ route('shop.checkout.cart.index') }}" class="hover:text-white">Panier</a></li>
                <li><a href="{{ route('shop.customers.account.index') }}" class="hover:text-white">Mon compte</a></li>
                <li><a href="{{ route('shop.home.index') }}#contact" class="hover:text-white">Contact</a></li>
            </ul>
        </div>

        <div>
            <h4 class="text-base font-semibold uppercase tracking-[0.2em] text-white">Rayons boissons</h4>

            <ul class="mt-5 grid gap-3 text-sm text-white/90">
                <li><a href="{{ route('shop.search.index', ['query' => 'whisky']) }}" class="hover:text-white">Whiskies</a></li>
                <li><a href="{{ route('shop.search.index', ['query' => 'vin']) }}" class="hover:text-white">Vins</a></li>
                <li><a href="{{ route('shop.search.index', ['query' => 'champagne']) }}" class="hover:text-white">Champagnes</a></li>
                <li><a href="{{ route('shop.search.index', ['query' => 'rhum']) }}" class="hover:text-white">Rhums</a></li>
                <li><a href="{{ route('shop.search.index', ['query' => 'aperitif']) }}" class="hover:text-white">Aperitifs</a></li>
            </ul>
        </div>

        <div id="contact">
            <h4 class="text-base font-semibold uppercase tracking-[0.2em] text-white">Contact</h4>

            <div class="mt-5 grid gap-4 text-sm text-white/90">
                <p>Lun - Sam: 8h30 - 20h00</p>
                <p><a href="mailto:daniel679091819@gmail.com" class="hover:text-white">daniel679091819@gmail.com</a></p>
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
