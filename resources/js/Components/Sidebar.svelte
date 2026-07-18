<script>
    import { Link, router, usePage } from '@inertiajs/svelte';
    import { LayoutDashboard, Receipt, Users, BarChart3, Settings, LogOut } from '@lucide/svelte';

    const page = usePage();

    const items = [
        { href: '/dashboard', label: 'Dashboard', icon: LayoutDashboard },
        { href: '/invoices', label: 'Receipts', icon: Receipt },
        { href: '/customers', label: 'Customers', icon: Users },
        { href: '/reports', label: 'Reports', icon: BarChart3 },
        { href: '/business/settings', label: 'Settings', icon: Settings },
    ];

    function logout(e) {
        e.preventDefault();
        router.post('/logout');
    }
</script>

<aside class="hidden sm:flex sm:flex-col sm:w-[104px] sm:shrink-0 bg-neutral-950 border-r border-white/10 min-h-[calc(100vh-4rem)]">
    <nav class="flex-1 flex flex-col items-stretch gap-1 py-4">
        {#each items as item (item.href)}
            {@const active = page.url.startsWith(item.href)}
            <Link
                href={item.href}
                class={`flex flex-col items-center gap-1.5 mx-2 rounded-lg py-2.5 text-xs font-medium transition-colors ${
                    active ? 'bg-white/10 text-white' : 'text-neutral-400 hover:text-white hover:bg-white/5'
                }`}
            >
                <item.icon class="h-5 w-5" strokeWidth={1.75} />
                {item.label}
            </Link>
        {/each}
    </nav>

    <div class="py-4 border-t border-white/10">
        <button
            type="button"
            onclick={logout}
            class="flex flex-col items-center gap-1.5 mx-2 rounded-lg py-2.5 text-xs font-medium text-neutral-400 hover:text-white hover:bg-white/5 transition-colors w-[calc(100%-1rem)]"
        >
            <LogOut class="h-5 w-5" strokeWidth={1.75} />
            Sign out
        </button>
    </div>
</aside>
