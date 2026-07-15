<script>
    import { Check, ChevronLeft, ChevronRight, Eye, X } from '@lucide/svelte';

    let { value = $bindable('ledger') } = $props();
    let carouselElement;
    let zoomedTemplate = $state(null);

    function scrollLeft() {
        if (carouselElement) carouselElement.scrollBy({ left: -250, behavior: 'smooth' });
    }

    function scrollRight() {
        if (carouselElement) carouselElement.scrollBy({ left: 250, behavior: 'smooth' });
    }

    const templates = [
        {
            id: 'ledger',
            name: 'Ledger',
            description: 'Consultancies, formal',
            svg: `<svg viewBox="0 0 100 130" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-neutral-800">
                    <rect width="100" height="130" rx="4" fill="white" stroke="#e5e5e5"/>
                    <circle cx="15" cy="15" r="4" fill="currentColor" fill-opacity="0.8"/>
                    <text x="23" y="16.5" font-family="serif" font-size="5" font-weight="bold" fill="currentColor">ACME CORP</text>
                    <text x="90" y="15" font-family="serif" font-size="4" fill="currentColor" fill-opacity="0.6" text-anchor="end">INVOICE</text>
                    <text x="90" y="20" font-family="sans-serif" font-size="3" fill="currentColor" fill-opacity="0.5" text-anchor="end">#INV-001</text>
                    <line x1="10" y1="28" x2="90" y2="28" stroke="currentColor" stroke-width="0.3"/>
                    <line x1="10" y1="29.5" x2="90" y2="29.5" stroke="currentColor" stroke-width="0.3"/>
                    <text x="10" y="38" font-family="sans-serif" font-size="3" fill="currentColor" fill-opacity="0.5">Billed To:</text>
                    <text x="10" y="42" font-family="serif" font-size="3.5" fill="currentColor">John Doe</text>
                    <rect x="10" y="50" width="80" height="6" fill="currentColor" fill-opacity="0.05"/>
                    <text x="12" y="54" font-family="sans-serif" font-size="2.5" fill="currentColor" fill-opacity="0.7">Description</text>
                    <text x="88" y="54" font-family="sans-serif" font-size="2.5" fill="currentColor" fill-opacity="0.7" text-anchor="end">Amount</text>
                    <text x="12" y="62" font-family="serif" font-size="3" fill="currentColor">Consulting Services</text>
                    <text x="88" y="62" font-family="sans-serif" font-size="3" fill="currentColor" text-anchor="end">$1,200.00</text>
                    <line x1="10" y1="65" x2="90" y2="65" stroke="currentColor" stroke-width="0.2" stroke-dasharray="1 1"/>
                    <text x="12" y="70" font-family="serif" font-size="3" fill="currentColor">Web Development</text>
                    <text x="88" y="70" font-family="sans-serif" font-size="3" fill="currentColor" text-anchor="end">$850.00</text>
                    <line x1="10" y1="73" x2="90" y2="73" stroke="currentColor" stroke-width="0.2" stroke-dasharray="1 1"/>
                    <line x1="60" y1="80" x2="90" y2="80" stroke="currentColor" stroke-width="0.3"/>
                    <text x="60" y="86" font-family="sans-serif" font-size="3" fill="currentColor" fill-opacity="0.6">Total</text>
                    <text x="88" y="86" font-family="serif" font-size="4" font-weight="bold" fill="currentColor" text-anchor="end">$2,050.00</text>
                    <line x1="60" y1="90" x2="90" y2="90" stroke="currentColor" stroke-width="0.5"/>
                    <line x1="60" y1="91.5" x2="90" y2="91.5" stroke="currentColor" stroke-width="0.5"/>
                  </svg>`
        },
        {
            id: 'bold',
            name: 'Bold',
            description: 'Agencies, startups',
            svg: `<svg viewBox="0 0 100 130" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-neutral-800">
                    <rect width="100" height="130" rx="4" fill="white" stroke="#e5e5e5"/>
                    <rect width="100" height="25" fill="currentColor" fill-opacity="0.9"/>
                    <rect x="10" y="8" width="8" height="8" fill="white"/>
                    <text x="22" y="14" font-family="sans-serif" font-size="6" font-weight="900" fill="white">ACME</text>
                    <text x="90" y="14" font-family="sans-serif" font-size="5" font-weight="900" fill="white" text-anchor="end">INVOICE</text>
                    <text x="10" y="35" font-family="sans-serif" font-size="3" font-weight="bold" fill="currentColor">BILL TO:</text>
                    <text x="10" y="40" font-family="sans-serif" font-size="3.5" fill="currentColor">John Doe</text>
                    <text x="90" y="35" font-family="sans-serif" font-size="3" font-weight="bold" fill="currentColor" text-anchor="end">DUE DATE</text>
                    <text x="90" y="40" font-family="sans-serif" font-size="3.5" fill="currentColor" text-anchor="end">Oct 24, 2026</text>
                    <rect x="10" y="50" width="80" height="7" fill="currentColor" fill-opacity="0.1"/>
                    <text x="12" y="55" font-family="sans-serif" font-size="2.5" font-weight="bold" fill="currentColor">ITEM</text>
                    <text x="88" y="55" font-family="sans-serif" font-size="2.5" font-weight="bold" fill="currentColor" text-anchor="end">TOTAL</text>
                    <text x="12" y="65" font-family="sans-serif" font-size="3" font-weight="bold" fill="currentColor">Branding Package</text>
                    <text x="88" y="65" font-family="sans-serif" font-size="3" fill="currentColor" text-anchor="end">$4,500.00</text>
                    <text x="12" y="75" font-family="sans-serif" font-size="3" font-weight="bold" fill="currentColor">SEO Audit</text>
                    <text x="88" y="75" font-family="sans-serif" font-size="3" fill="currentColor" text-anchor="end">$800.00</text>
                    <rect x="10" y="90" width="80" height="15" fill="currentColor" fill-opacity="0.9"/>
                    <text x="15" y="100" font-family="sans-serif" font-size="4" font-weight="bold" fill="white">TOTAL DUE</text>
                    <text x="85" y="100" font-family="sans-serif" font-size="5" font-weight="900" fill="white" text-anchor="end">$5,300.00</text>
                  </svg>`
        },
        {
            id: 'swiss',
            name: 'Swiss',
            description: 'Designers, minimal',
            svg: `<svg viewBox="0 0 100 130" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-neutral-800">
                    <rect width="100" height="130" rx="4" fill="white" stroke="#e5e5e5"/>
                    <rect x="10" y="10" width="8" height="8" fill="#ef4444"/>
                    <text x="22" y="16" font-family="sans-serif" font-size="5" font-weight="bold" fill="currentColor">ACME</text>
                    <line x1="10" y1="25" x2="90" y2="25" stroke="currentColor" stroke-width="0.2"/>
                    <text x="10" y="32" font-family="sans-serif" font-size="2.5" fill="currentColor" fill-opacity="0.5">Invoice No.</text>
                    <text x="10" y="36" font-family="sans-serif" font-size="3" fill="currentColor">1042</text>
                    <text x="35" y="32" font-family="sans-serif" font-size="2.5" fill="currentColor" fill-opacity="0.5">Date</text>
                    <text x="35" y="36" font-family="sans-serif" font-size="3" fill="currentColor">24.10.26</text>
                    <text x="60" y="32" font-family="sans-serif" font-size="2.5" fill="currentColor" fill-opacity="0.5">Client</text>
                    <text x="60" y="36" font-family="sans-serif" font-size="3" fill="currentColor">John Doe</text>
                    <line x1="10" y1="42" x2="90" y2="42" stroke="currentColor" stroke-width="0.2"/>
                    <text x="10" y="52" font-family="sans-serif" font-size="3" fill="currentColor">Architecture Draft</text>
                    <text x="90" y="52" font-family="sans-serif" font-size="3" fill="currentColor" text-anchor="end">1 200.00</text>
                    <line x1="10" y1="56" x2="90" y2="56" stroke="currentColor" stroke-width="0.1"/>
                    <text x="10" y="62" font-family="sans-serif" font-size="3" fill="currentColor">Structural Review</text>
                    <text x="90" y="62" font-family="sans-serif" font-size="3" fill="currentColor" text-anchor="end">450.00</text>
                    <line x1="10" y1="66" x2="90" y2="66" stroke="currentColor" stroke-width="0.1"/>
                    <line x1="10" y1="90" x2="90" y2="90" stroke="currentColor" stroke-width="0.5"/>
                    <text x="10" y="98" font-family="sans-serif" font-size="3.5" font-weight="bold" fill="currentColor">Total</text>
                    <text x="90" y="98" font-family="sans-serif" font-size="4" font-weight="bold" fill="currentColor" text-anchor="end">USD 1 650.00</text>
                  </svg>`
        },
        {
            id: 'thermal',
            name: 'Thermal',
            description: 'Retail, quick service',
            svg: `<svg viewBox="0 0 100 130" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-neutral-800">
                    <rect x="20" y="0" width="60" height="130" fill="#fcfcfc" stroke="#e5e5e5" stroke-dasharray="2 2"/>
                    <circle cx="50" cy="15" r="5" fill="currentColor" fill-opacity="0.8"/>
                    <text x="50" y="26" font-family="monospace" font-size="4" font-weight="bold" fill="currentColor" text-anchor="middle">ACME COFFEE</text>
                    <line x1="25" y1="32" x2="75" y2="32" stroke="currentColor" stroke-width="0.5" stroke-dasharray="1 2"/>
                    <text x="25" y="40" font-family="monospace" font-size="3" fill="currentColor">1x ESPRESSO</text>
                    <text x="75" y="40" font-family="monospace" font-size="3" fill="currentColor" text-anchor="end">$3.50</text>
                    <text x="25" y="46" font-family="monospace" font-size="3" fill="currentColor">2x CROISSANT</text>
                    <text x="75" y="46" font-family="monospace" font-size="3" fill="currentColor" text-anchor="end">$7.00</text>
                    <text x="25" y="52" font-family="monospace" font-size="3" fill="currentColor">1x LATTE</text>
                    <text x="75" y="52" font-family="monospace" font-size="3" fill="currentColor" text-anchor="end">$4.50</text>
                    <line x1="25" y1="58" x2="75" y2="58" stroke="currentColor" stroke-width="0.5" stroke-dasharray="1 2"/>
                    <text x="25" y="66" font-family="monospace" font-size="3.5" font-weight="bold" fill="currentColor">TOTAL</text>
                    <text x="75" y="66" font-family="monospace" font-size="4" font-weight="bold" fill="currentColor" text-anchor="end">$15.00</text>
                    <line x1="25" y1="72" x2="75" y2="72" stroke="currentColor" stroke-width="0.5" stroke-dasharray="1 2"/>
                    <text x="50" y="80" font-family="monospace" font-size="2.5" fill="currentColor" text-anchor="middle">THANK YOU!</text>
                    <rect x="30" y="85" width="2" height="15" fill="currentColor"/>
                    <rect x="33" y="85" width="1" height="15" fill="currentColor"/>
                    <rect x="36" y="85" width="3" height="15" fill="currentColor"/>
                    <rect x="41" y="85" width="1" height="15" fill="currentColor"/>
                    <rect x="44" y="85" width="2" height="15" fill="currentColor"/>
                    <rect x="47" y="85" width="4" height="15" fill="currentColor"/>
                    <rect x="53" y="85" width="1" height="15" fill="currentColor"/>
                    <rect x="56" y="85" width="2" height="15" fill="currentColor"/>
                    <rect x="60" y="85" width="3" height="15" fill="currentColor"/>
                    <rect x="65" y="85" width="1" height="15" fill="currentColor"/>
                    <rect x="68" y="85" width="2" height="15" fill="currentColor"/>
                  </svg>`
        },
        {
            id: 'friendly',
            name: 'Friendly',
            description: 'Freelancers, salons',
            svg: `<svg viewBox="0 0 100 130" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-neutral-800">
                    <rect width="100" height="130" rx="4" fill="white" stroke="#e5e5e5"/>
                    <rect x="10" y="10" width="12" height="12" rx="4" fill="#3b82f6"/>
                    <text x="25" y="18" font-family="sans-serif" font-size="5" font-weight="bold" fill="currentColor">Acme</text>
                    <rect x="70" y="12" width="20" height="8" rx="4" fill="#dbeafe"/>
                    <text x="80" y="17.5" font-family="sans-serif" font-size="3" font-weight="bold" fill="#2563eb" text-anchor="middle">Invoice</text>
                    <text x="10" y="32" font-family="sans-serif" font-size="3" fill="currentColor" fill-opacity="0.5">Billed to</text>
                    <text x="10" y="37" font-family="sans-serif" font-size="4" font-weight="bold" fill="currentColor">John Doe</text>
                    <rect x="10" y="45" width="80" height="35" rx="6" fill="#f8fafc" stroke="#e2e8f0" stroke-width="0.5"/>
                    <text x="14" y="52" font-family="sans-serif" font-size="2.5" fill="currentColor" fill-opacity="0.5">Item</text>
                    <text x="86" y="52" font-family="sans-serif" font-size="2.5" fill="currentColor" fill-opacity="0.5" text-anchor="end">Amount</text>
                    <text x="14" y="60" font-family="sans-serif" font-size="3" font-weight="bold" fill="currentColor">Haircut</text>
                    <text x="86" y="60" font-family="sans-serif" font-size="3" fill="currentColor" text-anchor="end">$45.00</text>
                    <text x="14" y="70" font-family="sans-serif" font-size="3" font-weight="bold" fill="currentColor">Beard Trim</text>
                    <text x="86" y="70" font-family="sans-serif" font-size="3" fill="currentColor" text-anchor="end">$20.00</text>
                    <rect x="10" y="85" width="80" height="20" rx="6" fill="#f3f4f6"/>
                    <text x="16" y="97" font-family="sans-serif" font-size="3.5" font-weight="bold" fill="currentColor">Total</text>
                    <text x="84" y="97" font-family="sans-serif" font-size="4.5" font-weight="bold" fill="currentColor" text-anchor="end">$65.00</text>
                  </svg>`
        },
        {
            id: 'sidebar',
            name: 'Sidebar',
            description: 'Studios, boutiques',
            svg: `<svg viewBox="0 0 100 130" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-neutral-800">
                    <rect width="100" height="130" rx="4" fill="white" stroke="#e5e5e5"/>
                    <rect x="0" y="0" width="30" height="130" fill="#f0fdf4"/>
                    <path d="M 10 15 L 20 15 L 15 25 Z" fill="#16a34a"/>
                    <text x="15" y="32" font-family="serif" font-size="3.5" font-weight="bold" fill="#166534" text-anchor="middle">ACME</text>
                    <text x="15" y="80" font-family="sans-serif" font-size="2.5" fill="#166534" text-anchor="middle">TOTAL DUE</text>
                    <text x="15" y="87" font-family="sans-serif" font-size="4" font-weight="bold" fill="#14532d" text-anchor="middle">$1,200</text>
                    <text x="38" y="18" font-family="sans-serif" font-size="5" font-weight="bold" fill="currentColor">INVOICE</text>
                    <text x="38" y="24" font-family="sans-serif" font-size="3" fill="currentColor" fill-opacity="0.5">#INV-2026</text>
                    <text x="38" y="38" font-family="sans-serif" font-size="2.5" fill="currentColor" fill-opacity="0.5">Billed to:</text>
                    <text x="38" y="43" font-family="serif" font-size="3.5" fill="currentColor">John Doe</text>
                    <line x1="38" y1="52" x2="90" y2="52" stroke="currentColor" stroke-width="0.2"/>
                    <text x="38" y="60" font-family="serif" font-size="3" fill="currentColor">Photography</text>
                    <text x="90" y="60" font-family="sans-serif" font-size="3" fill="currentColor" text-anchor="end">$800</text>
                    <text x="38" y="70" font-family="serif" font-size="3" fill="currentColor">Editing</text>
                    <text x="90" y="70" font-family="sans-serif" font-size="3" fill="currentColor" text-anchor="end">$400</text>
                    <line x1="38" y1="76" x2="90" y2="76" stroke="currentColor" stroke-width="0.2"/>
                  </svg>`
        },
        {
            id: 'premium',
            name: 'Premium',
            description: 'Luxe, high-end',
            svg: `<svg viewBox="0 0 100 130" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                    <rect width="100" height="130" rx="4" fill="#f6ecd9" stroke="#c9a227" stroke-width="0.5"/>
                    <circle cx="95" cy="5" r="38" fill="#7a1620" stroke="#c9a227" stroke-width="1"/>
                    <circle cx="5" cy="125" r="38" fill="#7a1620" stroke="#c9a227" stroke-width="1"/>
                    <polygon points="17,10 24,14 24,22 17,26 10,22 10,14" fill="#c9a227"/>
                    <rect x="10" y="34" width="55" height="9" fill="#1c140a" fill-opacity="0.85"/>
                    <line x1="10" y1="50" x2="90" y2="50" stroke="#c9a227" stroke-width="1"/>
                    <rect x="10" y="58" width="30" height="3" fill="currentColor" class="text-neutral-400" fill-opacity="0.5"/>
                    <rect x="55" y="58" width="30" height="3" fill="currentColor" class="text-neutral-400" fill-opacity="0.5"/>
                    <rect x="10" y="70" width="80" height="20" fill="none" stroke="#c9a227" stroke-width="0.75"/>
                    <rect x="10" y="100" width="35" height="6" fill="currentColor" class="text-neutral-400" fill-opacity="0.4"/>
                    <line x1="60" y1="108" x2="90" y2="108" stroke="#c9a227" stroke-width="0.75"/>
                  </svg>`
        }
    ];

    function selectTemplate(id) {
        value = id;
    }
</script>

<div class="relative group">
    <button type="button" onclick={scrollLeft} class="absolute left-0 top-[40%] -translate-y-1/2 -ml-3 z-10 bg-white border shadow-md rounded-full p-1.5 hidden sm:group-hover:block hover:bg-neutral-50 text-neutral-600 transition-opacity">
        <ChevronLeft class="w-5 h-5" />
    </button>

    <div bind:this={carouselElement} class="flex overflow-x-auto gap-4 pb-4 snap-x [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
        {#each templates as template}
            <div
                role="button"
                tabindex="0"
                onclick={() => selectTemplate(template.id)}
                onkeydown={(e) => e.key === 'Enter' && selectTemplate(template.id)}
                class="group/card cursor-pointer relative flex flex-col items-center p-2 rounded-lg border-2 text-left transition-all hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-neutral-900 focus:ring-offset-2 {value === template.id ? 'border-neutral-900 bg-neutral-50 shadow-sm' : 'border-neutral-200 bg-white'} shrink-0 w-[28%] md:w-[26%] lg:w-[24%] snap-start"
            >
                {#if value === template.id}
                    <div class="absolute top-2 right-2 bg-neutral-900 text-white rounded-full p-0.5 z-10">
                        <Check class="w-3 h-3" strokeWidth={3} />
                    </div>
                {/if}
                <div class="relative w-full aspect-[1/1.3] mb-3 bg-neutral-50 rounded-md overflow-hidden p-2 group-hover/card:bg-neutral-100 transition-colors">
                    {@html template.svg}
                    <button
                        type="button"
                        onclick={(e) => {
                            e.stopPropagation();
                            zoomedTemplate = template;
                        }}
                        class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover/card:opacity-100 transition-opacity"
                    >
                        <div class="bg-white rounded-full p-2.5 text-neutral-900 shadow-xl hover:scale-110 transition-transform">
                            <Eye class="w-5 h-5" />
                        </div>
                    </button>
                </div>
                <div class="w-full">
                    <p class="font-medium text-sm text-neutral-900">{template.name}</p>
                    <p class="text-xs text-neutral-500 mt-0.5">{template.description}</p>
                </div>
            </div>
        {/each}
    </div>

    <button type="button" onclick={scrollRight} class="absolute right-0 top-[40%] -translate-y-1/2 -mr-3 z-10 bg-white border shadow-md rounded-full p-1.5 hidden sm:group-hover:block hover:bg-neutral-50 text-neutral-600 transition-opacity">
        <ChevronRight class="w-5 h-5" />
    </button>
</div>

{#if zoomedTemplate}
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" onclick={() => zoomedTemplate = null}>
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden" onclick={(e) => e.stopPropagation()}>
            <div class="flex items-center justify-between p-4 border-b">
                <div>
                    <h3 class="font-bold text-lg">{zoomedTemplate.name}</h3>
                    <p class="text-sm text-neutral-500">{zoomedTemplate.description}</p>
                </div>
                <button type="button" onclick={() => zoomedTemplate = null} class="p-2 hover:bg-neutral-100 rounded-full transition-colors">
                    <X class="w-5 h-5 text-neutral-500" />
                </button>
            </div>
            <div class="p-6 overflow-y-auto bg-neutral-50 flex justify-center">
                <div class="w-full max-w-[300px]">
                    {@html zoomedTemplate.svg}
                </div>
            </div>
            <div class="p-4 border-t bg-white flex justify-end">
                <button type="button" class="px-4 py-2 bg-neutral-900 text-white rounded-md font-medium hover:bg-neutral-800 transition-colors" onclick={() => { selectTemplate(zoomedTemplate.id); zoomedTemplate = null; }}>
                    Select this template
                </button>
            </div>
        </div>
    </div>
{/if}
