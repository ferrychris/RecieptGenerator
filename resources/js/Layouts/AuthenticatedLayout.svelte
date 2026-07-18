<script>
    import { Link, router, usePage } from '@inertiajs/svelte';
    import ApplicationLogo from '../Components/ApplicationLogo.svelte';
    import OrgSwitcher from '../Components/OrgSwitcher.svelte';
    import Sidebar from '../Components/Sidebar.svelte';
    import MobileNavBar from '../Components/MobileNavBar.svelte';
    import { ChevronDown } from '@lucide/svelte';
    import { Toaster, toast } from 'svelte-sonner';

    let { header = undefined, children } = $props();

    let showingNavigationDropdown = $state(false);
    let showingUserDropdown = $state(false);

    const page = usePage();
    const user = $derived(page.props.auth?.user);
    const flash = $derived(page.props.flash);

    $effect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
        if (flash?.message) toast(flash.message);
    });

    function logout(e) {
        e.preventDefault();
        router.post('/logout');
    }
</script>

<div class="dark h-screen bg-neutral-950 text-white flex flex-col overflow-hidden">
    <Toaster theme="dark" position="top-right" richColors />
    <nav class="bg-neutral-950 border-b border-white/10 shrink-0">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="shrink-0 flex items-center gap-2">
                        <Link href="/dashboard" class="flex items-center gap-2">
                            <ApplicationLogo class="block h-8.5 w-auto fill-current text-white" />
                        </Link>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ms-2">
                        <OrgSwitcher />
                    </div>

                    <div class="hidden space-x-8 sm:-my-px sm:ms-8 sm:flex">

                    </div>
                </div>

                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <div class="relative">
                        <button
                            onclick={() => (showingUserDropdown = !showingUserDropdown)}
                            class="inline-flex items-center gap-1 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-neutral-200 hover:text-white hover:bg-white/10 focus:outline-none transition ease-in-out duration-150"
                        >
                            <div>{user?.name}</div>
                            <ChevronDown class="h-4 w-4" />
                        </button>

                        {#if showingUserDropdown}
                            <div
                                class="absolute z-50 mt-2 w-48 rounded-md shadow-lg ltr:origin-top-right rtl:origin-top-left end-0"
                                role="button"
                                tabindex="-1"
                                onclick={() => (showingUserDropdown = false)}
                            >
                                <div class="rounded-lg border border-white/10 py-1 bg-neutral-900 shadow-xl overflow-hidden">
                                    <Link
                                        href="/profile"
                                        class="block w-full px-4 py-2 text-start text-sm leading-5 text-neutral-300 hover:bg-white/5 hover:text-white focus:outline-none focus:bg-white/5 focus:text-white transition duration-150 ease-in-out"
                                    >
                                        Profile
                                    </Link>

                                    <Link
                                        href="/business/settings"
                                        class="block w-full px-4 py-2 text-start text-sm leading-5 text-neutral-300 hover:bg-white/5 hover:text-white focus:outline-none focus:bg-white/5 focus:text-white transition duration-150 ease-in-out"
                                    >
                                        Business settings
                                    </Link>

                                    <button
                                        onclick={logout}
                                        class="block w-full px-4 py-2 text-start text-sm leading-5 text-neutral-300 hover:bg-white/5 hover:text-white focus:outline-none focus:bg-white/5 focus:text-white transition duration-150 ease-in-out"
                                    >
                                        Log Out
                                    </button>
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button
                        onclick={() => (showingNavigationDropdown = !showingNavigationDropdown)}
                        class="inline-flex items-center justify-center p-2 rounded-md text-neutral-400 hover:text-white hover:bg-white/10 focus:outline-none focus:bg-white/10 focus:text-white transition duration-150 ease-in-out"
                    >
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path
                                class={showingNavigationDropdown ? 'hidden' : 'inline-flex'}
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                            <path
                                class={showingNavigationDropdown ? 'inline-flex' : 'hidden'}
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div class={showingNavigationDropdown ? 'block sm:hidden' : 'hidden'}>
            <div class="px-4 pt-3">
                <OrgSwitcher />
            </div>

            <div class="pt-2 pb-3 space-y-1">
                {#each [
                    ['/dashboard', 'Dashboard'],
                    ['/invoices', 'Receipts'],
                    ['/customers', 'Customers'],
                    ['/reports', 'Reports'],
                ] as [href, label] (href)}
                    <Link
                        {href}
                        class={`block w-full ps-3 pe-4 py-2 border-l-4 text-start text-base font-medium transition duration-150 ease-in-out focus:outline-none ${
                            page.url.startsWith(href)
                                ? 'border-indigo-400 text-white bg-white/5'
                                : 'border-transparent text-neutral-400 hover:text-white hover:bg-white/5 hover:border-neutral-600'
                        }`}
                    >
                        {label}
                    </Link>
                {/each}
            </div>

            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-white/10">
                <div class="px-4">
                    <div class="font-medium text-base text-white">{user?.name}</div>
                    <div class="font-medium text-sm text-neutral-400">{user?.email}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <Link
                        href="/profile"
                        class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-neutral-400 hover:text-white hover:bg-white/5 hover:border-neutral-600 focus:outline-none transition duration-150 ease-in-out"
                    >
                        Profile
                    </Link>

                    <Link
                        href="/business/settings"
                        class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-neutral-400 hover:text-white hover:bg-white/5 hover:border-neutral-600 focus:outline-none transition duration-150 ease-in-out"
                    >
                        Business settings
                    </Link>

                    <button
                        onclick={logout}
                        class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-neutral-400 hover:text-white hover:bg-white/5 hover:border-neutral-600 focus:outline-none transition duration-150 ease-in-out"
                    >
                        Log Out
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex flex-1 overflow-hidden">
        <Sidebar />

        <div class="flex-1 min-w-0 overflow-y-auto">
            <!-- Page Heading -->
            {#if header}
                <header class="border-b border-white/10">
                    <div class="px-4 sm:px-6 lg:px-8 py-6 max-w-7xl mx-auto w-full">
                        {@render header()}
                    </div>
                </header>
            {/if}

            <!-- Page Content -->
            <main class="px-4 sm:px-6 lg:px-8 py-8 pb-24 sm:pb-8 max-w-7xl mx-auto w-full">
                {@render children?.()}
            </main>
        </div>
    </div>

    <MobileNavBar />
</div>
