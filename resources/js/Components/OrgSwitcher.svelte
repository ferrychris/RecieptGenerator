<script>
    import { Link, router, usePage } from '@inertiajs/svelte';

    const page = usePage();
    const organizations = $derived(page.props.organizations ?? []);
    const currentBusinessId = $derived(page.props.auth?.user?.business_id);
    const current = $derived(organizations.find((org) => org.id === currentBusinessId));

    let showing = $state(false);

    function switchTo(org) {
        showing = false;
        if (org.id === currentBusinessId) return;
        router.post('/business/switch', { business_id: org.id });
    }
</script>

<div class="relative">
    <button
        onclick={() => (showing = !showing)}
        class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-neutral-200 hover:text-white hover:bg-white/10 focus:outline-none transition ease-in-out duration-150"
    >
        {#if current?.logo_full_url}
            <img src={current.logo_full_url} alt="" class="h-5 w-5 rounded object-cover" />
        {/if}
        <span class="max-w-[10rem] truncate">{current?.name ?? 'Select organization'}</span>
        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    {#if showing}
        <div
            class="absolute z-50 mt-2 w-64 rounded-md shadow-lg ltr:origin-top-left rtl:origin-top-right start-0"
            role="button"
            tabindex="-1"
            onclick={() => (showing = false)}
        >
            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                <div class="px-4 py-2 text-xs font-medium text-gray-400 uppercase tracking-wide">Organizations</div>

                {#each organizations as org (org.id)}
                    <button
                        onclick={() => switchTo(org)}
                        class="flex w-full items-center gap-2 px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                    >
                        {#if org.logo_full_url}
                            <img src={org.logo_full_url} alt="" class="h-5 w-5 rounded object-cover" />
                        {:else}
                            <span class="h-5 w-5 rounded bg-gray-200 shrink-0"></span>
                        {/if}
                        <span class="flex-1 truncate">{org.name}</span>
                        {#if org.id === currentBusinessId}
                            <svg class="h-4 w-4 text-indigo-600 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                        {/if}
                    </button>
                {/each}

                <div class="border-t border-gray-100 mt-1 pt-1">
                    <Link
                        href="/business/create"
                        class="block w-full px-4 py-2 text-start text-sm leading-5 text-indigo-600 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                    >
                        + New organization
                    </Link>
                </div>
            </div>
        </div>
    {/if}
</div>
