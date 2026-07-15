<script>
    import { Link } from '@inertiajs/svelte';
    import ApplicationLogo from '../Components/ApplicationLogo.svelte';
    import { 
        DocumentTextIcon, 
        Squares2x2Icon, 
        WalletIcon, 
        BuildingOfficeIcon, 
        SparklesIcon, 
        ArrowRightIcon, 
        CheckIcon 
    } from 'heroicons-svelte/24/outline';

    let activeStep = $state(0);
    let tick = $state(0);

    const templates = [
        { name: 'Ledger', accent: 'from-neutral-400 to-neutral-600' },
        { name: 'Swiss', accent: 'from-rose-400 to-red-600' },
        { name: 'Bold', accent: 'from-indigo-400 to-violet-600' },
        { name: 'Thermal', accent: 'from-emerald-400 to-teal-600' },
    ];

    const orgs = ['Acme Studio', 'Northwind Cafe', 'Riverside Repairs'];

    const stats = [
        { value: '7', label: 'receipt templates' },
        { value: '<10s', label: 'to generate a PDF' },
        { value: '4+', label: 'currencies supported' },
    ];

    // Automate timeline:
    // Step 0: Type customer name (3s / 30 ticks)
    // Step 1: Add items one by one (3s / 30 ticks)
    // Step 2: Hover & click "Create Receipt" button (2s / 20 ticks)
    // Step 3: Transition to Template Select and click "Swiss" template (3s / 30 ticks)
    // Step 4: Show Loading screen "Generating PDF..." (2s / 20 ticks)
    // Step 5: Show Swiss PDF Receipt preview (4s / 40 ticks)
    // Step 6: Show Payments Tracker. Animate payments Unpaid -> Part -> Paid (5s / 50 ticks)
    const stepDelays = [3000, 3000, 2000, 3000, 2000, 4000, 5000];

    $effect(() => {
        let activeTimer;
        
        function runStep() {
            activeTimer = setTimeout(() => {
                activeStep = (activeStep + 1) % stepDelays.length;
                tick = 0; // reset tick for the new step
                runStep();
            }, stepDelays[activeStep]);
        }
        
        runStep();
        
        const tickTimer = setInterval(() => {
            tick++;
        }, 100);

        return () => {
            clearTimeout(activeTimer);
            clearInterval(tickTimer);
        };
    });

    // Reactive State Derivations
    let typedCustomer = $derived.by(() => {
        if (activeStep !== 0) return 'Alex Carter';
        const name = 'Alex Carter';
        const chars = Math.min(name.length, Math.floor(tick / 1.5));
        return name.slice(0, chars);
    });

    let currentItems = $derived.by(() => {
        if (activeStep === 0) return [];
        if (activeStep === 1) {
            if (tick < 8) return [['Website design', '1 × $650']];
            if (tick < 18) return [['Website design', '1 × $650'], ['Hosting (12 mo)', '1 × $120']];
            return [['Website design', '1 × $650'], ['Hosting (12 mo)', '1 × $120'], ['Consulting', '2 × $150']];
        }
        return [['Website design', '1 × $650'], ['Hosting (12 mo)', '1 × $120'], ['Consulting', '2 × $150']];
    });

    let currentTotal = $derived.by(() => {
        const count = currentItems.length;
        if (count === 0) return '0.00';
        if (count === 1) return '650.00';
        if (count === 2) return '770.00';
        return '1,070.00';
    });

    let isCreateClicked = $derived(activeStep === 2 && tick > 10 && tick < 14);

    let selectedTemplate = $derived.by(() => {
        if (activeStep < 3) return -1;
        if (activeStep === 3) {
            return tick > 15 ? 1 : -1; // 1 is Swiss template
        }
        return 1;
    });

    let paymentStatus = $derived.by(() => {
        if (activeStep < 6) return 'unpaid';
        if (tick < 15) return 'unpaid';
        if (tick < 35) return 'part_payment';
        return 'paid';
    });

    let amountPaid = $derived.by(() => {
        if (activeStep < 6) return '0.00';
        if (tick < 15) return '0.00';
        if (tick < 35) return '400.00';
        return '1,070.00';
    });

    let balanceDue = $derived.by(() => {
        if (activeStep < 6) return '1,070.00';
        if (tick < 15) return '1,070.00';
        if (tick < 35) return '670.00';
        return '0.00';
    });

    // Virtual Cursor tracking
    let cursor = $derived.by(() => {
        let x = 50;
        let y = 50;
        let visible = false;
        let click = false;

        const currentTick = tick % 25; // 0 to 24

        if (activeStep === 0) {
            // Typing customer name
            visible = true;
            if (tick < 5) {
                x = 50; y = 50;
            } else if (tick < 12) {
                // Animate cursor moving to customer field (around y=20)
                const pct = (tick - 5) / 7;
                x = 50;
                y = 50 + (22 - 50) * pct;
            } else {
                x = 50; y = 22;
                if (tick === 12 || tick === 13) click = true;
            }
        } else if (activeStep === 1) {
            // Adding items (button is at x=78, y=65)
            visible = true;
            if (tick < 5) {
                x = 50; y = 22;
            } else if (tick < 10) {
                const pct = (tick - 5) / 5;
                x = 50 + (78 - 50) * pct;
                y = 22 + (65 - 22) * pct;
            } else if (tick < 18) {
                x = 78; y = 65;
                if (tick === 10 || tick === 11) click = true;
            } else {
                x = 78; y = 65;
                if (tick === 18 || tick === 19) click = true;
            }
        } else if (activeStep === 2) {
            // Click "Create Receipt" button (at x=50, y=85)
            visible = true;
            if (tick < 8) {
                const pct = tick / 8;
                x = 78 + (50 - 78) * pct;
                y = 65 + (85 - 65) * pct;
            } else {
                x = 50; y = 85;
                if (tick === 11 || tick === 12) click = true;
            }
        } else if (activeStep === 3) {
            // Template selection screen. Click "Swiss" template (at x=72, y=36)
            visible = true;
            if (tick < 10) {
                const pct = tick / 10;
                x = 50 + (72 - 50) * pct;
                y = 85 + (36 - 85) * pct;
            } else {
                x = 72; y = 36;
                if (tick === 14 || tick === 15) click = true;
            }
        } else if (activeStep === 6) {
            // Record payment (at x=50, y=82)
            visible = true;
            const stepTick = tick % 30;
            if (stepTick < 5) {
                x = 50; y = 80;
            } else if (stepTick < 10) {
                const progress = (stepTick - 5) / 5;
                x = 50;
                y = 80 + (70 - 80) * progress;
            } else {
                x = 50; y = 70;
                if (stepTick === 10 || stepTick === 11 || stepTick === 20 || stepTick === 21) click = true;
            }
        }

        return { x, y, visible, click };
    });
</script>

<svelte:head>
    <title>ReceiptGen — Receipts and invoices, done right</title>
</svelte:head>

<div class="dark relative min-h-screen overflow-x-clip bg-neutral-950 text-white antialiased">
    <!-- ambient backdrop: radial glow + grid -->
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute left-1/2 top-[-320px] h-[640px] w-[900px] -translate-x-1/2 rounded-full bg-blue-600/20 blur-[140px]"></div>
        <div class="absolute right-[-200px] top-[240px] h-[420px] w-[420px] rounded-full bg-violet-600/10 blur-[120px]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.035)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.035)_1px,transparent_1px)] bg-[size:72px_72px] [mask-image:radial-gradient(ellipse_70%_60%_at_50%_0%,black,transparent)]"></div>
    </div>

    <!-- nav -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-neutral-950/80 backdrop-blur-md border-b border-white/10">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <div class="flex items-center">
                <ApplicationLogo class="h-9 w-auto fill-current text-white" />
            </div>
            <nav class="flex items-center gap-2">
                <Link href="/login" class="rounded-full px-4 py-2 text-sm font-medium text-neutral-300 transition-colors hover:bg-white/5 hover:text-white">Sign in</Link>
                <Link href="/register" class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-neutral-950 shadow-[0_0_20px_rgba(255,255,255,0.15)] transition hover:bg-neutral-200">
                    Get started
                </Link>
            </nav>
        </div>
    </header>    <!-- hero -->
    <main class="relative z-10">
        <section class="mx-auto max-w-6xl px-6 pb-36 pt-28 sm:pt-36">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Left Column: Automated Phone Mockup Preview -->
                <div class="lg:col-span-7 flex justify-center items-center order-2 lg:order-1 relative py-6">
                    <!-- Phone Mockup -->
                    <div class="relative w-full max-w-[290px] aspect-[9/18.5] rounded-[48px] border-[8px] border-neutral-800 bg-neutral-950 p-4 shadow-2xl overflow-hidden flex flex-col shrink-0">
                        <!-- Dynamic Island / Notch -->
                        <div class="absolute top-2 left-1/2 -translate-x-1/2 w-24 h-4.5 bg-neutral-800 rounded-full z-30 flex items-center justify-center">
                            <div class="w-2.5 h-2.5 bg-neutral-900 rounded-full ml-auto mr-2"></div>
                        </div>

                        <!-- Phone Top Bar -->
                        <div class="flex justify-between items-center px-2 pt-1.5 pb-3 text-[10px] font-semibold text-neutral-400 select-none shrink-0 z-20">
                            <span>9:41</span>
                            <div class="flex items-center gap-1.5">
                                <!-- Cellular Signal Strength -->
                                <div class="flex items-end gap-[1.5px] h-2">
                                    <div class="w-[2px] h-[3px] bg-neutral-400 rounded-2xs"></div>
                                    <div class="w-[2px] h-[5px] bg-neutral-400 rounded-2xs"></div>
                                    <div class="w-[2px] h-[7px] bg-neutral-400 rounded-2xs"></div>
                                    <div class="w-[2px] h-[9px] bg-neutral-400 rounded-2xs"></div>
                                </div>
                                <!-- Wifi -->
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                    <path d="M12 21a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm-6.3-6.3a9 9 0 0 1 12.6 0l-1.4 1.4a7 7 0 0 0-9.8 0l-1.4-1.4zm-2.8-2.8a13 13 0 0 1 18.2 0l-1.4 1.4a11 11 0 0 0-15.4 0l-1.4-1.4zm-2.9-2.9a17 17 0 0 1 24 0l-1.4 1.4a15 15 0 0 0-21.2 0l-1.4-1.4z"/>
                                </svg>
                                <!-- Battery -->
                                <div class="w-5 h-2.5 border border-neutral-400 rounded-[3px] p-[1px] flex items-center">
                                    <div class="h-full w-full bg-neutral-400 rounded-[1.5px]"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Phone Content (Scrollable Container) -->
                        <div class="flex-1 rounded-[32px] overflow-hidden bg-neutral-900/60 p-3 pt-6 relative text-left select-none">
                            
                            <!-- Virtual Cursor -->
                            {#if cursor.visible}
                                <div 
                                    class="absolute w-4 h-4 bg-orange-500/80 rounded-full border border-white pointer-events-none z-50 transition-all duration-300 ease-out" 
                                    style="left: {cursor.x}%; top: {cursor.y}%; transform: translate(-50%, -50%) scale({cursor.click ? 0.75 : 1});"
                                >
                                    {#if cursor.click}
                                        <span class="absolute inset-0 rounded-full bg-orange-400/50 animate-ping"></span>
                                    {/if}
                                </div>
                            {/if}

                            <!-- 1. Create Receipt Screen -->
                            {#if activeStep <= 2}
                                <div class="rounded-xl border border-white/10 bg-neutral-950/90 p-4 shadow-xl flex flex-col h-full justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-1.5 text-[11px] font-medium text-neutral-300">
                                                <span class="flex size-6 items-center justify-center rounded-lg bg-orange-500/15 text-orange-500"><DocumentTextIcon class="size-3" /></span>
                                                New receipt
                                            </div>
                                            <span class="text-[9px] text-neutral-500">Draft</span>
                                        </div>
                                        
                                        <!-- Mock Form Inputs -->
                                        <div class="mt-4 space-y-3">
                                            <div>
                                                <span class="text-[8px] text-neutral-500 block">Customer</span>
                                                <div class="mt-1 w-full bg-neutral-900 border border-white/5 rounded-md px-2 py-1 text-[10px] text-white min-h-[20px] flex items-center">
                                                    {typedCustomer}
                                                    {#if activeStep === 0 && (tick % 6) < 3}
                                                        <span class="w-[1.5px] h-3 bg-orange-500 ml-0.5 animate-pulse"></span>
                                                    {/if}
                                                </div>
                                            </div>

                                            <div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-[8px] text-neutral-500 block">Items</span>
                                                    <button class="text-[8px] text-orange-500 font-semibold flex items-center gap-0.5 hover:text-orange-400 transition-colors">
                                                        + Add
                                                    </button>
                                                </div>
                                                <div class="mt-1 space-y-1.5">
                                                    {#each currentItems as [desc, qty]}
                                                        <div class="flex items-center justify-between text-[10px] bg-neutral-900/40 px-2 py-1 rounded border border-white/5">
                                                            <span class="text-neutral-200 truncate max-w-[120px]">{desc}</span>
                                                            <span class="tabular-nums text-neutral-500">{qty}</span>
                                                        </div>
                                                    {/each}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between">
                                        <div>
                                            <span class="text-[9px] text-neutral-500">Total</span>
                                            <div class="text-xs font-semibold tabular-nums text-white">${currentTotal}</div>
                                        </div>
                                        <button 
                                            class="px-3 py-1.5 rounded-lg text-[9px] font-semibold transition-all {isCreateClicked ? 'bg-orange-600 scale-95 shadow-[0_0_12px_rgba(249,115,22,0.4)]' : 'bg-orange-500 text-white shadow-md'}"
                                        >
                                            Create Receipt
                                        </button>
                                    </div>
                                </div>
                            {/if}

                            <!-- 2. Templates Screen -->
                            {#if activeStep === 3}
                                <div class="flex flex-col h-full justify-between">
                                    <div class="grid grid-cols-2 gap-2">
                                        {#each templates as t, i (t.name)}
                                            <div class={`rounded-xl border p-2 transition-all duration-300 ${i === selectedTemplate ? 'border-orange-500/80 bg-orange-500/10 shadow-[0_0_12px_rgba(249,115,22,0.2)]' : 'border-white/10 bg-neutral-950/90'}`}>
                                                <div class={`h-14 w-full rounded-md bg-gradient-to-br ${t.accent} opacity-60`}></div>
                                                <div class="mt-1.5 flex items-center justify-between px-0.5">
                                                    <span class="text-[9px] font-medium text-white">{t.name}</span>
                                                    {#if i === selectedTemplate}<CheckIcon class="size-2.5 text-orange-500" />{/if}
                                                </div>
                                            </div>
                                        {/each}
                                    </div>
                                    <p class="text-center text-[9px] text-neutral-500 mt-2 leading-relaxed">Choose a layout. Swiss has been selected.</p>
                                </div>
                            {/if}

                            <!-- 3. Loading Screen -->
                            {#if activeStep === 4}
                                <div class="flex flex-col items-center justify-center h-full py-16 text-center">
                                    <div class="w-8 h-8 border-2 border-orange-500 border-t-transparent rounded-full animate-spin"></div>
                                    <p class="text-[10px] text-neutral-400 mt-3 font-medium">Generating PDF...</p>
                                </div>
                            {/if}

                            <!-- 4. PDF Preview Screen -->
                            {#if activeStep === 5}
                                <div class="rounded-xl border border-neutral-200 bg-white text-neutral-900 p-3 shadow-xl text-[8px] font-sans h-full flex flex-col justify-between relative overflow-hidden select-none">
                                    
                                    <!-- Swiss Corner Tag / Stripe -->
                                    <div class="absolute -top-6 -right-6 w-12 h-12 bg-rose-600 rotate-45 z-10"></div>
                                    
                                    <!-- Receipt Header -->
                                    <div class="relative z-20">
                                        <div class="flex justify-between items-start pb-2 border-b-2 border-neutral-900">
                                            <div>
                                                <h3 class="font-black text-[11px] text-rose-600 tracking-tighter uppercase">ReceiptGen</h3>
                                                <p class="text-neutral-500 text-[6px] tracking-wide uppercase mt-0.5">Raytime Digitals</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-extrabold text-[9px] tracking-wider text-neutral-900">RECEIPT</p>
                                                <p class="text-neutral-500 text-[6px] font-mono mt-0.5">#RCT-00232</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Metadata Grid -->
                                        <div class="grid grid-cols-2 gap-2 my-2.5 pb-2 border-b border-neutral-100 text-[6px] text-neutral-500">
                                            <div>
                                                <span class="text-[5px] uppercase block font-bold text-neutral-400">Billed to:</span>
                                                <p class="font-extrabold text-neutral-900 text-[7px] mt-0.5">Alex Carter</p>
                                                <p class="text-neutral-400">alex@carter.com</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-[5px] uppercase block font-bold text-neutral-400">Issued Date:</span>
                                                <p class="font-bold text-neutral-900 mt-0.5">15 July 2026</p>
                                                <p class="text-neutral-400">Due: Upon receipt</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Items Table -->
                                        <div class="space-y-1.5 mt-2">
                                            <div class="flex justify-between font-bold border-b border-neutral-900 pb-1 text-[6px] text-neutral-400 uppercase">
                                                <span>Item description</span>
                                                <div class="flex gap-4">
                                                    <span class="w-6 text-right">Qty</span>
                                                    <span class="w-10 text-right">Total</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Row 1 -->
                                            <div class="flex justify-between items-center text-neutral-700 font-medium py-0.5 border-b border-neutral-100/50">
                                                <span class="truncate max-w-[110px] text-neutral-900 font-bold">Website design</span>
                                                <div class="flex gap-4 text-[6.5px]">
                                                    <span class="w-6 text-right text-neutral-400">1</span>
                                                    <span class="w-10 text-right font-bold">$650.00</span>
                                                </div>
                                            </div>
                                            <!-- Row 2 -->
                                            <div class="flex justify-between items-center text-neutral-700 font-medium py-0.5 border-b border-neutral-100/50">
                                                <span class="truncate max-w-[110px] text-neutral-900 font-bold">Hosting (12 mo)</span>
                                                <div class="flex gap-4 text-[6.5px]">
                                                    <span class="w-6 text-right text-neutral-400">1</span>
                                                    <span class="w-10 text-right font-bold">$120.00</span>
                                                </div>
                                            </div>
                                            <!-- Row 3 -->
                                            <div class="flex justify-between items-center text-neutral-700 font-medium py-0.5 border-b border-neutral-100/50">
                                                <span class="truncate max-w-[110px] text-neutral-900 font-bold">Consulting</span>
                                                <div class="flex gap-4 text-[6.5px]">
                                                    <span class="w-6 text-right text-neutral-400">2</span>
                                                    <span class="w-10 text-right font-bold">$300.00</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Tear-off & Total section -->
                                    <div class="border-t-2 border-dashed border-neutral-300 pt-2.5 mt-2 bg-neutral-50/50 -mx-3 -mb-3 p-3 flex flex-col justify-end">
                                        <div class="flex justify-between items-center text-[7px] text-neutral-500 mb-1">
                                            <span>Subtotal</span>
                                            <span class="font-bold text-neutral-800">$1,070.00</span>
                                        </div>
                                        <div class="flex justify-between items-center text-[7px] text-neutral-500 mb-1.5">
                                            <span>Tax (0.0%)</span>
                                            <span class="font-bold text-neutral-800">$0.00</span>
                                        </div>
                                        <div class="flex justify-between items-center border-t border-neutral-200 pt-1.5">
                                            <span class="font-extrabold text-[8px] text-neutral-900 uppercase tracking-wider">Total Amount</span>
                                            <span class="font-black text-[11px] text-rose-600">$1,070.00</span>
                                        </div>
                                        
                                        <!-- Barcode representation -->
                                        <div class="mt-2.5 text-neutral-400">
                                            <svg class="h-3.5 w-full opacity-60" viewBox="0 0 100 20" preserveAspectRatio="none">
                                                <rect x="0" y="0" width="2" height="20" fill="currentColor"/>
                                                <rect x="3" y="0" width="1" height="20" fill="currentColor"/>
                                                <rect x="5" y="0" width="3" height="20" fill="currentColor"/>
                                                <rect x="9" y="0" width="1" height="20" fill="currentColor"/>
                                                <rect x="11" y="0" width="2" height="20" fill="currentColor"/>
                                                <rect x="15" y="0" width="4" height="20" fill="currentColor"/>
                                                <rect x="20" y="0" width="1" height="20" fill="currentColor"/>
                                                <rect x="22" y="0" width="2" height="20" fill="currentColor"/>
                                                <rect x="26" y="0" width="3" height="20" fill="currentColor"/>
                                                <rect x="30" y="0" width="1" height="20" fill="currentColor"/>
                                                <rect x="32" y="0" width="2" height="20" fill="currentColor"/>
                                                <rect x="36" y="0" width="1" height="20" fill="currentColor"/>
                                                <rect x="38" y="0" width="4" height="20" fill="currentColor"/>
                                                <rect x="43" y="0" width="2" height="20" fill="currentColor"/>
                                                <rect x="46" y="0" width="1" height="20" fill="currentColor"/>
                                                <rect x="49" y="0" width="3" height="20" fill="currentColor"/>
                                                <rect x="53" y="0" width="1" height="20" fill="currentColor"/>
                                                <rect x="55" y="0" width="2" height="20" fill="currentColor"/>
                                                <rect x="59" y="0" width="4" height="20" fill="currentColor"/>
                                                <rect x="64" y="0" width="1" height="20" fill="currentColor"/>
                                                <rect x="66" y="0" width="2" height="20" fill="currentColor"/>
                                                <rect x="70" y="0" width="3" height="20" fill="currentColor"/>
                                                <rect x="74" y="0" width="1" height="20" fill="currentColor"/>
                                                <rect x="76" y="0" width="2" height="20" fill="currentColor"/>
                                                <rect x="80" y="0" width="1" height="20" fill="currentColor"/>
                                                <rect x="82" y="0" width="3" height="20" fill="currentColor"/>
                                                <rect x="86" y="0" width="2" height="20" fill="currentColor"/>
                                                <rect x="89" y="0" width="1" height="20" fill="currentColor"/>
                                                <rect x="91" y="0" width="4" height="20" fill="currentColor"/>
                                                <rect x="96" y="0" width="1" height="20" fill="currentColor"/>
                                                <rect x="98" y="0" width="2" height="20" fill="currentColor"/>
                                            </svg>
                                            <div class="text-[5px] text-center font-mono mt-0.5 tracking-widest uppercase">RCT-00232-9426</div>
                                        </div>
                                    </div>
                                    
                                </div>
                            {/if}

                            <!-- 5. Payment Tracking Screen -->
                            {#if activeStep === 6}
                                <div class="rounded-xl border border-white/10 bg-neutral-950/90 p-4 shadow-xl flex flex-col h-full justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="text-[10px] font-medium text-white">RCT-00232</div>
                                                <div class="mt-0.5 text-[8px] text-neutral-500">Alex Carter</div>
                                            </div>
                                            <span class={`inline-flex rounded-full px-2 py-0.5 text-[8px] font-semibold ring-1 transition-all ${
                                                paymentStatus === 'unpaid' ? 'bg-red-500/10 text-red-400 ring-red-500/20' :
                                                paymentStatus === 'part_payment' ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20' :
                                                'bg-green-500/10 text-green-400 ring-green-500/20'
                                            }`}>
                                                {paymentStatus === 'unpaid' ? 'Unpaid' : paymentStatus === 'part_payment' ? 'Part Payment' : 'Paid'}
                                            </span>
                                        </div>

                                        <!-- Balances -->
                                        <div class="grid grid-cols-2 gap-2 mt-4">
                                            <div class="bg-neutral-900 px-2.5 py-1.5 rounded-lg border border-white/5">
                                                <div class="text-[7px] text-neutral-500">Paid</div>
                                                <div class="text-xs font-semibold tabular-nums text-white">${amountPaid}</div>
                                            </div>
                                            <div class="bg-neutral-900 px-2.5 py-1.5 rounded-lg border border-white/5">
                                                <div class="text-[7px] text-neutral-500">Due</div>
                                                <div class="text-xs font-semibold tabular-nums text-white">${balanceDue}</div>
                                            </div>
                                        </div>

                                        <!-- Transactions log -->
                                        <div class="mt-4">
                                            <span class="text-[8px] text-neutral-500 block mb-1.5">Transactions</span>
                                            <div class="space-y-1.5 max-h-[80px] overflow-y-auto">
                                                {#if paymentStatus !== 'unpaid'}
                                                    <div class="flex justify-between items-center text-[8px] bg-neutral-900/40 px-2 py-1 rounded border border-white/5">
                                                        <div>
                                                            <div class="text-neutral-300 font-medium">Deposit</div>
                                                            <div class="text-[6px] text-neutral-500">10:00 AM</div>
                                                        </div>
                                                        <span class="text-green-400 font-semibold">+$400.00</span>
                                                    </div>
                                                {/if}
                                                {#if paymentStatus === 'paid'}
                                                    <div class="flex justify-between items-center text-[8px] bg-neutral-900/40 px-2 py-1 rounded border border-white/5">
                                                        <div>
                                                            <div class="text-neutral-300 font-medium">Final Payment</div>
                                                            <div class="text-[6px] text-neutral-500">10:15 AM</div>
                                                        </div>
                                                        <span class="text-green-400 font-semibold">+$670.00</span>
                                                    </div>
                                                {/if}
                                                {#if paymentStatus === 'unpaid'}
                                                    <p class="text-[8px] text-neutral-600 text-center py-2">No transactions recorded</p>
                                                {/if}
                                            </div>
                                        </div>
                                    </div>

                                    <button class="w-full py-1.5 bg-orange-500 hover:bg-orange-600 text-[9px] font-semibold text-white rounded-lg shadow-md transition-all mt-4">
                                        Record Payment
                                    </button>
                                </div>
                            {/if}

                        </div>

                        <!-- Home Indicator -->
                        <div class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-32 h-1 bg-neutral-800 rounded-full z-30"></div>
                    </div>
                </div>

                <!-- Right Column: Copy & Actions -->
                <div class="lg:col-span-5 flex flex-col items-start text-left order-1 lg:order-2">
                    
                    <!-- NEW Badge -->
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.04] px-3.5 py-1.5 text-xs font-medium text-neutral-300 backdrop-blur">
                        <SparklesIcon class="size-3.5 text-orange-500" />
                        <span class="text-neutral-300">New — run multiple businesses from one account</span>
                    </div>

                    <!-- Heading -->
                    <h1 class="text-balance bg-gradient-to-b from-white via-white to-neutral-400 bg-clip-text text-4xl font-semibold leading-[1.08] tracking-tight text-transparent sm:text-5xl lg:text-6xl">
                        Professional receipts,<br /> generated in seconds
                    </h1>

                    <!-- Paragraph -->
                    <p class="mt-5 text-base leading-relaxed text-neutral-400">
                        Create branded receipts and invoices, track payments as they come in, and switch between businesses — all from one clean dashboard.
                    </p>

                    <!-- Star Ratings -->
                    <div class="mt-6 flex flex-wrap gap-2.5">
                        <div class="inline-flex items-center gap-1 rounded-full border border-white/5 bg-white/[0.02] px-3 py-1 text-xs text-neutral-300">
                            <span class="text-orange-500">★</span> 4.9 <span class="text-neutral-500 ml-0.5">ease of use</span>
                        </div>
                        <div class="inline-flex items-center gap-1 rounded-full border border-white/5 bg-white/[0.02] px-3 py-1 text-xs text-neutral-300">
                            <span class="text-orange-500">★</span> 4.8 <span class="text-neutral-500 ml-0.5">speed</span>
                        </div>
                        <div class="inline-flex items-center gap-1 rounded-full border border-white/5 bg-white/[0.02] px-3 py-1 text-xs text-neutral-300">
                            <span class="text-orange-500">★</span> 4.9 <span class="text-neutral-500 ml-0.5">value</span>
                        </div>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <Link
                            href="/register"
                            class="group inline-flex h-11 items-center gap-2 rounded-full bg-orange-500 px-6 text-sm font-semibold text-white shadow-[0_8px_30px_rgba(249,115,22,0.35)] transition hover:bg-orange-600"
                        >
                            Get started
                            <ArrowRightIcon class="size-4 transition-transform group-hover:translate-x-0.5" />
                        </Link>
                        <Link
                            href="/login"
                            class="inline-flex h-11 items-center rounded-full border border-white/10 bg-white/[0.04] px-6 text-sm font-medium text-neutral-200 backdrop-blur transition hover:border-white/20 hover:bg-white/[0.08]"
                        >
                            Sign in
                        </Link>
                    </div>

                    <!-- Loved By section -->
                    <div class="mt-8 flex items-center gap-3">
                        <!-- Avatars -->
                        <div class="flex -space-x-2">
                            <img class="inline-block h-8 w-8 rounded-full ring-2 ring-neutral-950 object-cover animate-fade-in" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=80&h=80&fit=crop&crop=face" alt="User Avatar" />
                            <img class="inline-block h-8 w-8 rounded-full ring-2 ring-neutral-950 object-cover animate-fade-in" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=80&h=80&fit=crop&crop=face" alt="User Avatar" />
                            <img class="inline-block h-8 w-8 rounded-full ring-2 ring-neutral-950 object-cover animate-fade-in" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=80&h=80&fit=crop&crop=face" alt="User Avatar" />
                            <img class="inline-block h-8 w-8 rounded-full ring-2 ring-neutral-950 object-cover animate-fade-in" src="https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=80&h=80&fit=crop&crop=face" alt="User Avatar" />
                        </div>
                        <span class="text-xs text-neutral-400">loved by 30,000+ teams</span>
                    </div>

                </div>
            </div>

            <!-- Bottom Partner Logos -->
            <div class="mt-20 border-t border-white/[0.06] pt-10">
                <div class="flex flex-wrap items-center justify-between gap-x-8 gap-y-6 opacity-40">
                    <span class="text-sm font-bold tracking-wider uppercase text-neutral-400">Hexa</span>
                    <span class="text-sm font-bold tracking-wider uppercase text-neutral-400">Orbital</span>
                    <span class="text-sm font-bold tracking-wider uppercase text-neutral-400">Facet</span>
                    <span class="text-sm font-bold tracking-wider uppercase text-neutral-400">Stackline</span>
                    <span class="text-sm font-bold tracking-wider uppercase text-neutral-400">Wayline</span>
                    <span class="text-sm font-bold tracking-wider uppercase text-neutral-400">Curveo</span>
                </div>
            </div>
        </section>
    </main>

    <footer class="fixed bottom-0 left-0 right-0 z-50 bg-neutral-950/80 backdrop-blur-md border-t border-white/[0.06]">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4 text-xs sm:text-sm text-neutral-500">
            <span>&copy; {new Date().getFullYear()} ReceiptGen by <a href="https://raytimedigital.org" target="_blank" rel="noopener" class="underline hover:text-white transition-colors">Raytime Digitals</a></span>
            <div class="flex items-center gap-5">
                <Link href="/login" class="transition-colors hover:text-white">Sign in</Link>
                <Link href="/register" class="transition-colors hover:text-white">Create account</Link>
            </div>
        </div>
    </footer>
</div>
