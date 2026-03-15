{!! view_render_event('bagisto.shop.layout.footer.before') !!}

<!--
    The category repository is injected directly here because there is no way
    to retrieve it from the view composer, as this is an anonymous component.
-->
@inject('themeCustomizationRepository', 'Webkul\Theme\Repositories\ThemeCustomizationRepository')

<!--
    This code needs to be refactored to reduce the amount of PHP in the Blade
    template as much as possible.
-->
@php
    $channel = core()->getCurrentChannel();

    $customization = $themeCustomizationRepository->findOneWhere([
        'type'       => 'footer_links',
        'status'     => 1,
        'theme_code' => $channel->theme,
        'channel_id' => $channel->id,
    ]);
@endphp

<footer
    class="mt-10 border-t-4 border-[#F6B21A] bg-[#0A1F61] bg-cover bg-center text-white"
    style="background-image: linear-gradient(rgba(9, 28, 84, 0.9), rgba(9, 28, 84, 0.94)), url('{{ bagisto_asset('images/hero-image.jpg') }}');"
>
    <div class="mx-auto max-w-[1520px] px-6 py-12 md:px-12">
        <div class="grid gap-10 lg:grid-cols-[1.2fr_1fr_1fr_1fr]">
            <div>
                <a href="{{ route('shop.home.index') }}" class="inline-flex items-center gap-3">
                    <img
                        src="{{ asset('logodjouf.webp') }}"
                        alt="Djouf Inter"
                        class="h-16 w-auto"
                    >
                </a>

                <h3 class="mt-5 text-5xl font-semibold text-white max-md:text-3xl" style="font-family: 'DM Serif Display', serif;">
                    Djouf Inter
                </h3>

                <p class="mt-5 max-w-[430px] text-[30px] leading-8 text-white/90 max-md:text-base">
                    Votre boutique premium de boissons: whiskies, vins, champagnes, aperitifs et spiritueux. Qualite, authenticite et livraison rapide.
                </p>
            </div>

            <div>
                <h4 class="text-4xl font-semibold uppercase tracking-wide text-white">Navigation</h4>

                <ul class="mt-6 grid gap-4 text-lg text-white/90" v-pre>
                    <li><a href="{{ route('shop.home.index') }}" class="hover:text-[#F6B21A]">Accueil</a></li>
                    <li><a href="{{ route('shop.home.index') }}#featured-products" class="hover:text-[#F6B21A]">Nos boissons</a></li>
                    <li><a href="{{ route('shop.checkout.cart.index') }}" class="hover:text-[#F6B21A]">Panier</a></li>
                    <li><a href="{{ route('shop.customers.account.index') }}" class="hover:text-[#F6B21A]">Mon compte</a></li>
                    <li><a href="{{ route('shop.home.index') }}#contact" class="hover:text-[#F6B21A]">Contact</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-4xl font-semibold uppercase tracking-wide text-white">Rayons boissons</h4>

                <ul class="mt-6 grid gap-4 text-lg text-white/90" v-pre>
                    <li><a href="{{ route('shop.search.index', ['query' => 'whisky']) }}" class="hover:text-[#F6B21A]">Whiskies</a></li>
                    <li><a href="{{ route('shop.search.index', ['query' => 'vin']) }}" class="hover:text-[#F6B21A]">Vins</a></li>
                    <li><a href="{{ route('shop.search.index', ['query' => 'champagne']) }}" class="hover:text-[#F6B21A]">Champagnes</a></li>
                    <li><a href="{{ route('shop.search.index', ['query' => 'rhum']) }}" class="hover:text-[#F6B21A]">Rhums</a></li>
                    <li><a href="{{ route('shop.search.index', ['query' => 'aperitif']) }}" class="hover:text-[#F6B21A]">Aperitifs</a></li>
                </ul>
            </div>

            <div id="contact">
                <h4 class="text-4xl font-semibold uppercase tracking-wide text-white">Contact</h4>

                <div class="mt-6 grid gap-5 text-lg text-white/90">
                    <p class="flex items-start gap-3">
                        <span class="icon-calendar text-2xl text-[#F6B21A]"></span>
                        <span>Lun - Sam: 8h30 - 20h00</span>
                    </p>

                    <p class="flex items-start gap-3 break-all">
                        <span class="icon-envelope text-2xl text-[#F6B21A]"></span>
                        <a href="mailto:daniel679091819@gmail.com" class="hover:text-[#F6B21A]">daniel679091819@gmail.com</a>
                    </p>
                </div>

                <div class="mt-7 flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-full bg-white/15 text-xl hover:bg-white/25">f</span>
                    <span class="grid h-11 w-11 place-items-center rounded-full bg-white/15 text-xl hover:bg-white/25">i</span>
                    <span class="grid h-11 w-11 place-items-center rounded-full bg-white/15 text-xl hover:bg-white/25">▶</span>
                    <span class="grid h-11 w-11 place-items-center rounded-full bg-white/15 text-xl hover:bg-white/25">♪</span>
                </div>
            </div>
        </div>

        <div class="mt-10 border-t border-white/20 pt-7">
            <div class="flex flex-wrap items-center justify-center gap-6 text-xl font-semibold text-white/95 max-md:text-lg">
                <span>Selection premium de boissons</span>
                <span class="text-[#F6B21A]">|</span>
                <span>Vins &amp; Champagnes</span>
                <span class="text-[#F6B21A]">|</span>
                <span>Whiskies &amp; Rhums</span>
                <span class="text-[#F6B21A]">|</span>
                <span>Spiritueux &amp; Aperitifs</span>
            </div>

            <div class="mt-8 flex flex-wrap items-center justify-between gap-4 text-base text-white/85 max-md:text-sm">
                <p>
                    © {{ date('Y') }} Djouf Inter. Tous droits reserves.
                </p>

                <div class="flex items-center gap-4">
                    <a href="#" class="text-[#4DA7FF] hover:text-[#F6B21A]">Mentions legales</a>
                    <span class="text-white/40">|</span>
                    <a href="#" class="text-[#4DA7FF] hover:text-[#F6B21A]">Confidentialite</a>
                </div>
            </div>
        </div>
    </div>
</footer>

{!! view_render_event('bagisto.shop.layout.footer.after') !!}
