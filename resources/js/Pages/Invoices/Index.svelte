<script>
    import { Link } from '@inertiajs/svelte';
    import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.svelte';
    import { Card, CardContent } from '$lib/components/ui/card';
    import { Button } from '$lib/components/ui/button';

    let { invoices, filters } = $props();

    const statusStyles = {
        unpaid: 'bg-red-500/10 text-red-400',
        part_payment: 'bg-yellow-500/10 text-yellow-400',
        paid: 'bg-green-500/10 text-green-400',
    };

    function formatStatus(status) {
        return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    import { router } from '@inertiajs/svelte';
    import { Input } from '$lib/components/ui/input';

    let search = $state(filters?.search ?? '');
    let status = $state(filters?.status ?? '');
    let from = $state('');
    let to = $state('');
    let selected = $state([]);

    let deletableInvoices = $derived(invoices.data.filter(i => i.status !== 'paid'));
    let allSelected = $derived(deletableInvoices.length > 0 && selected.length === deletableInvoices.length);

    function toggleAll() {
        if (allSelected) {
            selected = [];
        } else {
            selected = deletableInvoices.map(i => i.id);
        }
    }

    function destroy(invoice) {
        if (confirm(`Delete receipt ${invoice.number}?`)) {
            router.delete(`/invoices/${invoice.id}`, {
                onSuccess: () => selected = selected.filter(id => id !== invoice.id),
            });
        }
    }

    function bulkDelete() {
        if (confirm(`Delete ${selected.length} receipts?`)) {
            router.post('/invoices/bulk-delete', { ids: selected }, {
                onSuccess: () => selected = [],
            });
        }
    }

    let timer;
    function applyFilters() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            router.get('/invoices', { search, status }, { preserveState: true, preserveScroll: true, replace: true });
        }, 300);
    }

    function exportTransactions() {
        const params = new URLSearchParams();
        if (from) params.set('from', from);
        if (to) params.set('to', to);
        window.location.href = `/invoices/export-transactions?${params.toString()}`;
    }
</script>

<svelte:head>
    <title>Receipts</title>
</svelte:head>

<AuthenticatedLayout>
    {#snippet header()}
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">Receipts</h2>
            <Link href="/invoices/create">
                <Button>New receipt</Button>
            </Link>
        </div>
    {/snippet}

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                <Input type="search" placeholder="Search by number or customer..." class="w-full sm:w-80" bind:value={search} oninput={applyFilters} />
                <select class="flex h-10 w-full sm:w-40 items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" bind:value={status} onchange={applyFilters}>
                    <option value="">All Statuses</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="part_payment">Part Payment</option>
                    <option value="paid">Paid</option>
                </select>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <Input type="date" bind:value={from} class="w-full sm:w-36" />
                    <Input type="date" bind:value={to} class="w-full sm:w-36" />
                    <Button variant="outline" size="sm" onclick={exportTransactions}>Export</Button>
                </div>
            </div>
            {#if selected.length > 0}
                <div class="flex items-center gap-2">
                    <span class="text-sm text-neutral-400">{selected.length} selected</span>
                    <Button variant="destructive" size="sm" onclick={bulkDelete}>Delete Selected</Button>
                </div>
            {/if}
        </div>

        <Card>
            <CardContent class="p-0">
                {#if invoices.data.length === 0}
                    <div class="p-6 text-sm text-neutral-400">No receipts found.</div>
                {:else}
                    <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-neutral-400 uppercase border-b border-white/10">
                            <tr>
                                <th class="px-6 py-3 w-10">
                                    <input type="checkbox" class="rounded border-white/20 bg-transparent text-neutral-900 focus:ring-neutral-900" checked={allSelected} onchange={toggleAll} />
                                </th>
                                <th class="px-6 py-3">Number</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Total</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each invoices.data as invoice (invoice.id)}
                                <tr class="border-b border-white/10 last:border-0 hover:bg-white/5">
                                    <td class="px-6 py-4">
                                        {#if invoice.status !== 'paid'}
                                            <input type="checkbox" class="rounded border-white/20 bg-transparent text-neutral-900 focus:ring-neutral-900 cursor-pointer" value={invoice.id} bind:group={selected} />
                                        {:else}
                                            <input type="checkbox" class="rounded border-white/10 bg-transparent text-neutral-800 cursor-not-allowed opacity-30" disabled />
                                        {/if}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-white">
                                        {#if invoice.status === 'paid'}
                                            <a href={`/invoices/${invoice.id}/preview`} class="hover:underline">{invoice.number}</a>
                                        {:else}
                                            <Link href={`/invoices/${invoice.id}/edit`} class="hover:underline">{invoice.number}</Link>
                                        {/if}
                                    </td>
                                    <td class="px-6 py-4 text-neutral-400">{invoice.customer?.name ?? '—'}</td>
                                    <td class="px-6 py-4 text-neutral-400">{invoice.issue_date}</td>
                                    <td class="px-6 py-4">
                                        <span class={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${statusStyles[invoice.status]}`}>
                                            {formatStatus(invoice.status)}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-white">{invoice.total} {invoice.currency}</td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            {#if invoice.status !== 'paid'}
                                                <Link href={`/invoices/${invoice.id}/edit`}>
                                                    <Button variant="outline" size="sm">Edit</Button>
                                                </Link>
                                            {/if}
                                            <a href={`/invoices/${invoice.id}/preview`} target="_blank" rel="noopener">
                                                <Button variant="outline" size="sm">Preview</Button>
                                            </a>
                                            <a href={`/invoices/${invoice.id}/pdf`} target="_blank" rel="noopener">
                                                <Button variant="outline" size="sm">Download</Button>
                                            </a>
                                            {#if invoice.status !== 'paid'}
                                                <Button variant="ghost" size="sm" onclick={() => destroy(invoice)}>Delete</Button>
                                            {/if}
                                        </div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                    </div>
                {/if}
            </CardContent>
        </Card>

        {#if invoices.links.length > 3}
            <div class="flex gap-1 flex-wrap">
                {#each invoices.links as link (link.label)}
                    {#if link.url}
                        <Link
                            href={link.url}
                            class={`px-3 py-1 rounded-md text-sm ${link.active ? 'bg-white/10 text-white' : 'bg-transparent border border-white/10 text-neutral-300 hover:bg-white/5'}`}
                        >
                            {@html link.label}
                        </Link>
                    {:else}
                        <span class="px-3 py-1 rounded-md text-sm text-neutral-600">{@html link.label}</span>
                    {/if}
                {/each}
            </div>
        {/if}
    </div>
</AuthenticatedLayout>
