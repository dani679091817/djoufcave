{!! view_render_event('bagisto.shop.layout.footer.before') !!}

<footer class="relative mt-12 overflow-hidden border-t border-white/20 text-white">
    <div
        class="absolute inset-0 bg-cover bg-center"
        style="background-image:url('{{ bagisto_asset('images/hero-image.jpg') }}');"
        aria-hidden="true"
    ></div>

    <div class="absolute inset-0 bg-gradient-to-r from-[#0d2f78]/95 via-[#153a88]/92 to-[#0a255f]/95" aria-hidden="true"></div>

    <div class="absolute inset-0 backdrop-blur-[1.5px]" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-[1520px] px-4 py-14 md:px-8 lg:px-12">
        <div class="grid gap-10 lg:grid-cols-[1.3fr_1fr_1fr_1fr]">
            <div>
                <a href="{{ route('shop.home.index') }}" class="inline-flex items-center gap-3" aria-label="Djouf Inter">
                    <span class="grid h-20 w-20 place-items-center rounded-full border-2 border-white/75 bg-white/15 p-1.5 shadow-lg shadow-black/25">
                        <img
                            src="{{ asset('logodjouf.webp') }}"
                            alt="Djouf Inter"
                            class="h-full w-full rounded-full object-cover"
                        >
                    </span>
                </a>

                <h3 class="mt-5 text-4xl font-semibold tracking-wide">Djouf Inter</h3>

                <p class="mt-5 max-w-[460px] text-base leading-7 text-white/90">
                    Votre boutique premium de boissons: whiskies, vins, champagnes, aperitifs et spiritueux. Qualite, authenticite et livraison rapide.
                </p>

                <div class="mt-6 flex items-center gap-3">
                    <a href="#" class="grid h-10 w-10 place-items-center rounded-full border border-white/35 bg-white/10 transition hover:bg-white/20" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current"><path d="M13.5 8.5V6.8c0-.5.4-.8.9-.8H16V3.2h-2.3c-2.5 0-3.7 1.5-3.7 3.8v1.5H8v2.9h2v8.4h3.5v-8.4H16l.4-2.9h-2.9Z"/></svg>
                    </a>

                    <a href="#" class="grid h-10 w-10 place-items-center rounded-full border border-white/35 bg-white/10 transition hover:bg-white/20" aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current"><path d="M7.8 3h8.4A4.8 4.8 0 0 1 21 7.8v8.4a4.8 4.8 0 0 1-4.8 4.8H7.8A4.8 4.8 0 0 1 3 16.2V7.8A4.8 4.8 0 0 1 7.8 3Zm0 1.8A3 3 0 0 0 4.8 7.8v8.4a3 3 0 0 0 3 3h8.4a3 3 0 0 0 3-3V7.8a3 3 0 0 0-3-3H7.8Zm8.9 1.3a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2ZM12 7.6a4.4 4.4 0 1 1 0 8.8 4.4 4.4 0 0 1 0-8.8Zm0 1.8a2.6 2.6 0 1 0 0 5.2 2.6 2.6 0 0 0 0-5.2Z"/></svg>
                    </a>

                    <a href="#" class="grid h-10 w-10 place-items-center rounded-full border border-white/35 bg-white/10 transition hover:bg-white/20" aria-label="YouTube">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current"><path d="M21.6 8.2a2.9 2.9 0 0 0-2-2c-1.8-.5-9.2-.5-9.2-.5s-7.4 0-9.2.5a2.9 2.9 0 0 0-2 2A30.7 30.7 0 0 0-1 12a30.7 30.7 0 0 0 .2 3.8 2.9 2.9 0 0 0 2 2c1.8.5 9.2.5 9.2.5s7.4 0 9.2-.5a2.9 2.9 0 0 0 2-2A30.7 30.7 0 0 0 22 12a30.7 30.7 0 0 0-.4-3.8ZM9.7 15.2V8.8l5.6 3.2-5.6 3.2Z"/></svg>
                    </a>

                    <a href="#" class="grid h-10 w-10 place-items-center rounded-full border border-white/35 bg-white/10 transition hover:bg-white/20" aria-label="TikTok">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current"><path d="M15.3 3c.4 2 1.6 3.3 3.7 3.5V9c-1.5 0-2.7-.5-3.7-1.3v5.4c0 3.2-2.2 5.5-5.5 5.5A5.5 5.5 0 0 1 4.2 13c0-3 2.4-5.5 5.6-5.5.4 0 .8 0 1.2.1V10a3.3 3.3 0 0 0-1.2-.2 3.1 3.1 0 0 0-3.3 3.2 3.1 3.1 0 0 0 3.3 3.2c1.9 0 3.2-1.2 3.2-3.4V3h2.3Z"/></svg>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="text-lg font-semibold uppercase tracking-[0.2em]">Navigation</h4>

                <ul class="mt-5 space-y-3 text-sm text-white/90">
                    <li><a href="{{ route('shop.home.index') }}" class="transition hover:text-white">Accueil</a></li>
                    <li><a href="{{ route('shop.home.index') }}#featured-products" class="transition hover:text-white">Nos boissons</a></li>
                    <li><a href="{{ route('shop.checkout.cart.index') }}" class="transition hover:text-white">Panier</a></li>
                    <li><a href="{{ route('shop.customers.account.index') }}" class="transition hover:text-white">Mon compte</a></li>
                    <li><a href="{{ route('shop.home.index') }}#contact" class="transition hover:text-white">Contact</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-lg font-semibold uppercase tracking-[0.2em]">Rayons boissons</h4>

                <ul class="mt-5 space-y-3 text-sm text-white/90">
                    <li><a href="{{ route('shop.search.index', ['query' => 'whisky']) }}" class="transition hover:text-white">Whiskies</a></li>
                    <li><a href="{{ route('shop.search.index', ['query' => 'vin']) }}" class="transition hover:text-white">Vins</a></li>
                    <li><a href="{{ route('shop.search.index', ['query' => 'champagne']) }}" class="transition hover:text-white">Champagnes</a></li>
                    <li><a href="{{ route('shop.search.index', ['query' => 'rhum']) }}" class="transition hover:text-white">Rhums</a></li>
                    <li><a href="{{ route('shop.search.index', ['query' => 'aperitif']) }}" class="transition hover:text-white">Aperitifs</a></li>
                </ul>
            </div>

            <div id="contact">
                <h4 class="text-lg font-semibold uppercase tracking-[0.2em]">Contact</h4>

                <div class="mt-5 space-y-4 text-sm text-white/90">
                    <p class="flex items-start gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="mt-0.5 h-5 w-5 flex-none stroke-current" stroke-width="1.8"><path d="M7 3v2m10-2v2M4 9h16M5.5 5h13A1.5 1.5 0 0 1 20 6.5v12a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 18.5v-12A1.5 1.5 0 0 1 5.5 5Z"/></svg>
                        <span>Lun - Sam: 8h30 - 20h00</span>
                    </p>

                    <p class="flex items-start gap-2.5 break-all">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="mt-0.5 h-5 w-5 flex-none stroke-current" stroke-width="1.8"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>
                        <a href="mailto:daniel679091819@gmail.com" class="transition hover:text-white">daniel679091819@gmail.com</a>
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-10 border-t border-white/25 pt-6">
            <div class="flex flex-col gap-4 text-sm lg:flex-row lg:items-center lg:justify-between">
                <p class="text-white/85">© 2026 Djouf Inter. Tous droits reserves.</p>

                <div class="flex flex-wrap items-center gap-3 text-white/95 lg:justify-center">
                    <span>Selection premium de boissons</span>
                    <span class="text-white/50">|</span>
                    <span>Vins &amp; Champagnes</span>
                    <span class="text-white/50">|</span>
                    <span>Whiskies &amp; Rhums</span>
                    <span class="text-white/50">|</span>
                    <span>Spiritueux &amp; Aperitifs</span>
                </div>

                <div class="flex items-center gap-3 text-sm">
                    <a href="#" class="text-white/85 transition hover:text-white">Mentions legales</a>
                    <span class="text-white/50">|</span>
                    <a href="#" class="text-white/85 transition hover:text-white">Confidentialite</a>
                </div>
            </div>
        </div>
    </div>
</footer>

{!! view_render_event('bagisto.shop.layout.footer.after') !!}
