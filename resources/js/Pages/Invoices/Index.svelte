<script>
    import { Link } from '@inertiajs/svelte';
    import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.svelte';
    import { Card, CardContent } from '$lib/components/ui/card';
    import { Button } from '$lib/components/ui/button';
    import ReceiptPreviewModal from '../../Components/ReceiptPreviewModal.svelte';

    let { invoices, filters } = $props();

    let previewing = $state(null);

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
                                            <button type="button" class="hover:underline" onclick={() => (previewing = invoice)}>{invoice.number}</button>
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
                                            <a href={`https://wa.me/${invoice.customer?.whatsapp_number ? invoice.customer.whatsapp_number.replace(/\D/g,'') : ''}?text=${encodeURIComponent(`Here is your receipt ${invoice.number}:\n${invoice.verify_url}`)}`} target="_blank" rel="noopener" title="Share via WhatsApp">
                                                <Button variant="outline" size="sm" class="bg-[#25D366]/10 text-[#25D366] hover:bg-[#25D366]/20 border-[#25D366]/20 px-2 sm:px-3">
                                                    <svg class="w-4 h-4 sm:mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                                    <span class="hidden sm:inline">WhatsApp</span>
                                                </Button>
                                            </a>
                                            <Button variant="outline" size="sm" onclick={() => (previewing = invoice)}>Preview</Button>
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

    <ReceiptPreviewModal invoice={previewing} onclose={() => (previewing = null)} />
</AuthenticatedLayout>
