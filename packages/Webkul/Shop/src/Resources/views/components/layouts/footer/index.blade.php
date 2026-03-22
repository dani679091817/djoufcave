<!--{!! view_render_event('bagisto.shop.layout.footer.before') !!}

@unless (request()->routeIs('shop.customer.session.create', 'shop.customers.register.index'))
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
            <p>
                <a
                    href="https://www.linkedin.com/in/daniel-otomo-a25239287"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="font-medium text-white hover:underline"
                >
                    Powered by OMD
                </a>
            </p>

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
@endunless

{!! view_render_event('bagisto.shop.layout.footer.after') !!}
-->
{!! view_render_event('bagisto.shop.layout.footer.before') !!}

@php
    $storeName = core()->getConfigData('sales.shipping.origin.store_name') ?: config('app.name');
    $storePhone = core()->getConfigData('sales.shipping.origin.contact_number');
    $storeStreet = core()->getConfigData('sales.shipping.origin.street_address');
    $storeCity = core()->getConfigData('sales.shipping.origin.city');
    $storeCountry = core()->getConfigData('sales.shipping.origin.country');
    $storeEmail = config('mail.from.address');

    $storeAddress = trim(collect([$storeStreet, $storeCity, $storeCountry])->filter()->implode(', '));
@endphp

<footer class="djouf-reference-footer">
    <div class="footer-wrap">
        <div class="footer-grid">
            <div class="footer-column">
                <a href="{{ route('shop.home.index') }}" class="flex items-center gap-3">
                    <img src="{{ asset('logodjouf.webp') }}" alt="Djouf Inter" class="footer-logo" />
                </a>

                <h3 class="footer-brand-title">Djouf Inter</h3>

                <p class="footer-copy">
                    Votre boutique premium de boissons: whiskies, vins, champagnes, aperitifs et spiritueux. Qualité, authenticité et livraison rapide.
                </p>

                <div class="footer-socials">
                    <a href="#" class="footer-social" aria-label="Facebook">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.998 12.042c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.988 4.388 10.952 10.124 11.852v-8.384H7.078v-3.469h3.044V9.413c0-3.006 1.792-4.666 4.533-4.666 1.313 0 2.686.234 2.686.234v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.384c5.736-.9 10.124-5.864 10.124-11.852z"/></svg>
                    </a>
                    <a href="#" class="footer-social" aria-label="Instagram">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.466.182-.8.398-1.15.748-.35.35-.566.684-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.398.8.748 1.15.35.35.684.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.684.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" fill-rule="evenodd"/></svg>
                    </a>
                    <a href="#" class="footer-social" aria-label="YouTube">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                    <a href="#" class="footer-social" aria-label="TikTok">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.1 1.75 2.9 2.9 0 0 1 2.31-4.64 2.88 2.88 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-.96-.1z"/></svg>
                    </a>
                </div>
            </div>

            <div class="footer-column">
                <h4 class="footer-title">NAVIGATION</h4>
                <ul class="footer-list">
                    <li><a href="{{ route('shop.home.index') }}">Accueil</a></li>
                    <li><a href="{{ route('shop.home.index') }}#featured-products">Nos boissons</a></li>
                    <li><a href="{{ route('shop.checkout.cart.index') }}">Panier</a></li>
                    <li><a href="{{ route('shop.customers.account.index') }}">Mon compte</a></li>
                    <li><a href="{{ route('shop.home.index') }}#contact">Contact</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h4 class="footer-title">RAYONS BOISSONS</h4>
                <ul class="footer-list">
                    <li><a href="{{ route('shop.search.index', ['query' => 'whisky']) }}">Whiskies</a></li>
                    <li><a href="{{ route('shop.search.index', ['query' => 'vin']) }}">Vins</a></li>
                    <li><a href="{{ route('shop.search.index', ['query' => 'champagne']) }}">Champagnes</a></li>
                    <li><a href="{{ route('shop.search.index', ['query' => 'rhum']) }}">Rhums</a></li>
                    <li><a href="{{ route('shop.search.index', ['query' => 'aperitif']) }}">Aperitifs</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h4 class="footer-title">CONTACT</h4>
                <div class="footer-list footer-contact-item">
                    <span class="footer-contact-icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <span>Lun - Sam: 8h30 - 20h00</span>
                </div>
                <div class="footer-list footer-contact-item">
                    <span class="footer-contact-icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <span><a href="mailto:{{ $storeEmail }}">{{ $storeEmail }}</a></span>
                </div>
            </div>
        </div>

        <div class="footer-divider"></div>

        <div class="footer-bottom">
            <div class="footer-patronage">
                <strong>Selection premium de boissons</strong>
                <span class="footer-separator">|</span>
                <span>Vins & Champagnes</span>
                <span class="footer-separator">|</span>
                <span>Whiskies & Rhums</span>
                <span class="footer-separator">|</span>
                <span>Spiritueux & Aperitifs</span>
            </div>

            <div class="footer-legal">
                <span>© {{ date('Y') }} {{ $storeName }}</span>
                <span class="footer-separator">|</span>
                <span>Tous droits réservés</span>
            </div>
        </div>

        <a href="#" class="footer-top-link" aria-label="Retour en haut">↑</a>
    </div>
</footer>

{!! view_render_event('bagisto.shop.layout.footer.after') !!}
