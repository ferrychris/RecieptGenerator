<script>
    import { onMount } from 'svelte';

    let { customers = [], value = $bindable(''), id = '', disabled = false } = $props();

    let open = $state(false);
    let search = $state('');
    let inputRef;

    let filteredCustomers = $derived(
        customers.filter(c => c.name.toLowerCase().includes(search.toLowerCase()))
    );

    let selectedCustomer = $derived(
        customers.find(c => c.id === value)
    );

    function selectCustomer(customerId) {
        value = customerId;
        open = false;
        search = '';
    }

    function handleWindowClick(e) {
        if (!e.target.closest('.customer-select-container')) {
            open = false;
        }
    }
</script>

<svelte:window onclick={handleWindowClick} />

<div class="relative w-full customer-select-container">
    <button
        type="button"
        {id}
        {disabled}
        class="mt-1 flex h-9 w-full items-center justify-between rounded-md border border-white/10 bg-neutral-950 text-white px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-50 transition-colors"
        onclick={() => { open = !open; if (open) setTimeout(() => inputRef?.focus(), 0); }}
    >
        <span class="truncate">
            {selectedCustomer ? selectedCustomer.name : 'Select a customer...'}
        </span>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 opacity-50"><polyline points="6 9 12 15 18 9"></polyline></svg>
    </button>

    {#if open && !disabled}
        <div class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border border-white/10 bg-neutral-900 p-1 text-base shadow-lg sm:text-sm text-white">
            <div class="sticky top-0 bg-neutral-900 px-2 py-1.5 pb-2 border-b border-white/5">
                <!-- svelte-ignore a11y_autofocus -->
                <input
                    bind:this={inputRef}
                    bind:value={search}
                    type="text"
                    placeholder="Search customers..."
                    class="flex h-8 w-full rounded-md border border-white/10 bg-neutral-950 text-white placeholder-neutral-500 px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-500"
                />
            </div>
            
            <div class="px-1 py-1">
                {#if filteredCustomers.length === 0}
                    <div class="relative cursor-default select-none py-2 px-2 text-sm text-neutral-400 text-center">
                        No customers found.
                    </div>
                {:else}
                    {#each filteredCustomers as customer (customer.id)}
                        <!-- svelte-ignore a11y_click_events_have_key_events -->
                        <!-- svelte-ignore a11y_no_static_element_interactions -->
                        <div
                            class="relative flex cursor-default select-none items-center rounded-sm py-1.5 px-2 text-sm outline-none hover:bg-neutral-800 hover:text-white cursor-pointer transition-colors {value === customer.id ? 'bg-neutral-800 font-medium text-white' : 'text-neutral-200'}"
                            onclick={() => selectCustomer(customer.id)}
                        >
                            {customer.name}
                        </div>
                    {/each}
                {/if}
            </div>
        </div>
    {/if}
</div>
