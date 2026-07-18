<script>
    import { Link, usePage } from '@inertiajs/svelte';
    import { LayoutDashboard, Receipt, Users, BarChart3, Settings } from '@lucide/svelte';

    const page = usePage();

    const items = [
        { href: '/dashboard', label: 'Home', icon: LayoutDashboard },
        { href: '/invoices', label: 'Receipts', icon: Receipt },
        { href: '/customers', label: 'Customers', icon: Users },
        { href: '/reports', label: 'Reports', icon: BarChart3 },
        { href: '/business/settings', label: 'Settings', icon: Settings },
    ];
</script>

<nav
    class="fixed inset-x-0 bottom-0 z-40 flex sm:hidden items-stretch justify-around border-t border-white/10 bg-neutral-950/95 backdrop-blur-md pb-[env(safe-area-inset-bottom)]"
    aria-label="Primary"
>
    {#each items as item (item.href)}
        {@const active = page.url.startsWith(item.href)}
        <Link
            href={item.href}
            class={`flex flex-1 flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium transition-colors ${
                active ? 'text-white' : 'text-neutral-500 hover:text-neutral-300'
            }`}
        >
            <item.icon class="h-5 w-5" strokeWidth={active ? 2 : 1.75} />
            {item.label}
        </Link>
    {/each}
</nav>
