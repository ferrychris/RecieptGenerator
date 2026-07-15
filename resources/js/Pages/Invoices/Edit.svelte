<script>
    import { useForm, router, Link } from '@inertiajs/svelte';
    import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.svelte';
    import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '$lib/components/ui/card';
    import { Label } from '$lib/components/ui/label';
    import { Input } from '$lib/components/ui/input';
    import { Button } from '$lib/components/ui/button';
    import InputError from '../../Components/InputError.svelte';
    import Checkbox from '../../Components/Checkbox.svelte';

    let { invoice } = $props();

    const isEditable = true;

    const form = useForm({
        customer_id: invoice.customer_id,
        currency: invoice.currency,
        issue_date: invoice.issue_date ? invoice.issue_date.substring(0, 10) : '',
        notes: invoice.notes ?? '',
        status: invoice.status,
        amount_paid: invoice.amount_paid ?? 0,
        paid_in_full: invoice.status === 'paid',
        items: invoice.items.map((item) => ({
            description: item.description,
            qty: item.qty,
            unit_price: item.unit_price,
            tax_rate: item.tax_rate,
        })),
    });

    function addItem() {
        form.items = [...form.items, { description: '', qty: 1, unit_price: 0, tax_rate: 0 }];
    }

    function removeItem(index) {
        if (form.items.length <= 1) return;
        form.items = form.items.filter((_, i) => i !== index);
    }

    let subtotal = $derived(form.items.reduce((sum, item) => sum + (Number(item.qty) || 0) * (Number(item.unit_price) || 0), 0));
    let taxTotal = $derived(
        form.items.reduce((sum, item) => sum + ((Number(item.qty) || 0) * (Number(item.unit_price) || 0) * (Number(item.tax_rate) || 0)) / 100, 0),
    );
    let total = $derived(subtotal + taxTotal);
    let amountPaid = $derived(form.status === 'paid' ? total : (form.status === 'part_payment' ? (Number(form.amount_paid) || 0) : 0));
    let balanceDue = $derived(Math.max(0, total - amountPaid));

    function money(n) {
        return (Number(n) || 0).toFixed(2);
    }

    function getCurrencySymbol(currencyCode) {
        const symbols = {
            'USD': '$',
            'NGN': '₦',
            'GBP': '£',
            'EUR': '€'
        };
        return symbols[currencyCode] || currencyCode;
    }

    function submit(e) {
        e.preventDefault();

        // Check if status or amount paid changed
        const statusChanged = form.status !== invoice.status;
        const amountChanged = Number(form.amount_paid) !== Number(invoice.amount_paid);

        if (statusChanged || amountChanged) {
            if (!confirm(`Are you sure you want to update the status and/or amount paid?`)) {
                return;
            }
        }

        form.patch(`/invoices/${invoice.id}`);
    }

    function destroy() {
        if (confirm(`Delete receipt ${invoice.number}?`)) {
            router.delete(`/invoices/${invoice.id}`);
        }
    }

    const statusStyles = {
        unpaid: 'bg-red-500/10 text-red-400',
        part_payment: 'bg-yellow-500/10 text-yellow-400',
        paid: 'bg-green-500/10 text-green-400',
    };

    function formatStatus(status) {
        return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        return new Date(dateStr).toLocaleString();
    }
</script>

<svelte:head>
    <title>Receipt {invoice.number}</title>
</svelte:head>

<AuthenticatedLayout>
    {#snippet header()}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h2 class="font-semibold text-xl text-white leading-tight">{invoice.number}</h2>
                <span class={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${statusStyles[invoice.status]}`}>{formatStatus(invoice.status)}</span>
            </div>
            <div class="flex gap-2">
                <a href={`/invoices/${invoice.id}/preview`} target="_blank" rel="noopener">
                    <Button variant="outline">Preview</Button>
                </a>
                <a href={`/invoices/${invoice.id}/pdf`} target="_blank" rel="noopener">
                    <Button variant="outline">Download PDF</Button>
                </a>
                {#if isEditable}
                    <Button variant="ghost" onclick={destroy}>Delete</Button>
                {/if}
            </div>
        </div>
    {/snippet}

    <div class="space-y-6">
        <form onsubmit={submit} class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Details</CardTitle>
                    <CardDescription>Customer: {invoice.customer?.name}</CardDescription>
                </CardHeader>
                <CardContent class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <Label for="currency">Currency</Label>
                        <select
                            id="currency"
                            bind:value={form.currency}
                            disabled={!isEditable}
                            required
                            class="mt-1 flex h-9 w-full rounded-md border border-white/10 bg-neutral-950 text-white px-3 py-1 text-sm shadow-sm disabled:cursor-not-allowed disabled:opacity-50 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-500 transition-colors"
                        >
                            <option value="USD">USD ($)</option>
                            <option value="NGN">NGN (₦)</option>
                            <option value="GBP">GBP (£)</option>
                            <option value="EUR">EUR (€)</option>
                        </select>
                        <InputError message={form.errors.currency} class="mt-2" />
                    </div>
                    <div>
                        <Label for="issue_date">Date</Label>
                        <Input id="issue_date" type="date" class="mt-1" bind:value={form.issue_date} disabled={!isEditable} required />
                        <InputError message={form.errors.issue_date} class="mt-2" />
                    </div>
                    <div>
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            bind:value={form.status}
                            disabled={!isEditable}
                            required
                            class="mt-1 flex h-9 w-full rounded-md border border-white/10 bg-neutral-950 text-white px-3 py-1 text-sm shadow-sm disabled:cursor-not-allowed disabled:opacity-50 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-500 transition-colors"
                        >
                            <option value="unpaid">Unpaid</option>
                            <option value="part_payment">Part Payment</option>
                            <option value="paid">Paid</option>
                        </select>
                        <InputError message={form.errors.status} class="mt-2" />
                    </div>
                    {#if form.status === 'part_payment'}
                        <div>
                            <Label for="amount_paid">Amount Paid</Label>
                            <Input id="amount_paid" type="number" min="0" step="0.01" class="mt-1" bind:value={form.amount_paid} disabled={!isEditable} required />
                            <InputError message={form.errors.amount_paid} class="mt-2" />
                        </div>
                    {/if}
                </CardContent>
            </Card>

            <div class="grid grid-cols-1 xl:grid-cols-[300px_minmax(0,1fr)] gap-6 items-start">
                <Card class="h-fit xl:sticky xl:top-4">
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Transaction History</CardTitle>
                            <CardDescription>
                                Log of status changes.
                            </CardDescription>
                        </div>
                    </CardHeader>

                    {#if invoice.transactions && invoice.transactions.length > 0}
                        <CardContent class="p-0 border-t border-white/10">
                            <div class="max-h-80 overflow-y-auto">
                            <table class="w-full text-[11px] sm:text-xs text-left">
                                <thead class="text-[10px] uppercase tracking-wide text-neutral-400 bg-white/5">
                                    <tr>
                                        <th scope="col" class="px-3 py-2">Date</th>
                                        <th scope="col" class="px-3 py-2">Status</th>
                                        <th scope="col" class="px-3 py-2">Amount</th>
                                        <th scope="col" class="px-3 py-2">Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {#each invoice.transactions as transaction (transaction.id)}
                                        <tr class="border-b border-white/10 last:border-0">
                                            <td class="px-3 py-2 text-neutral-400">{formatDate(transaction.created_at)}</td>
                                            <td class="px-3 py-2 font-medium text-white">{formatStatus(transaction.new_status)}</td>
                                            <td class="px-3 py-2 text-neutral-400">{transaction.amount ? getCurrencySymbol(invoice.currency) + money(transaction.amount) : '-'}</td>
                                            <td class="px-3 py-2 text-neutral-400">{transaction.note ?? ''}</td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                            </div>
                        </CardContent>
                    {:else}
                        <CardContent class="border-t border-white/10 pt-4 text-sm text-neutral-400">
                            No transactions recorded.
                        </CardContent>
                    {/if}
                </Card>

                <Card>
                <CardHeader>
                    <CardTitle>Line items</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    {#each form.items as item, index}
                        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                            <div class="flex-1 w-full">
                                <Label for={`item-${index}-desc`} class="sm:hidden mb-1 block">Description</Label>
                                <Input id={`item-${index}-desc`} bind:value={item.description} disabled={!isEditable} required placeholder="Item description" />
                            </div>
                            <div class="w-full sm:w-24">
                                <Label for={`item-${index}-qty`} class="sm:hidden mb-1 block">Qty</Label>
                                <Input id={`item-${index}-qty`} type="number" min="0.01" step="0.01" bind:value={item.qty} disabled={!isEditable} required />
                            </div>
                            <div class="w-full sm:w-32">
                                <Label for={`item-${index}-price`} class="sm:hidden mb-1 block">Price</Label>
                                <Input id={`item-${index}-price`} type="number" min="0" step="0.01" bind:value={item.unit_price} disabled={!isEditable} required />
                            </div>
                            <div class="w-full sm:w-24">
                                <Label for={`item-${index}-tax`} class="sm:hidden mb-1 block">Tax %</Label>
                                <Input id={`item-${index}-tax`} type="number" min="0" max="100" step="1" bind:value={item.tax_rate} disabled={!isEditable} />
                            </div>
                            {#if isEditable && form.items.length > 1}
                                <Button variant="ghost" size="icon" type="button" onclick={() => removeItem(index)} class="mt-6 sm:mt-0">
                                    <span class="sr-only">Remove item</span>
                                    &times;
                                </Button>
                            {/if}
                        </div>
                    {/each}
                    {#if isEditable}
                        <div class="mt-4">
                            <Button variant="outline" type="button" onclick={addItem}>Add item</Button>
                        </div>
                    {/if}
                </CardContent>
                <CardFooter class="flex flex-col items-end gap-1 border-t border-white/10 pt-4">
                    <div class="text-sm text-neutral-400">Subtotal: {getCurrencySymbol(form.currency)}{money(subtotal)}</div>
                    <div class="text-sm text-neutral-400">Tax: {getCurrencySymbol(form.currency)}{money(taxTotal)}</div>
                    <div class="text-base font-semibold text-white">Total: {getCurrencySymbol(form.currency)}{money(total)}</div>
                    {#if form.status === 'part_payment'}
                        <div class="text-sm text-neutral-400">Amount Paid: -{getCurrencySymbol(form.currency)}{money(amountPaid)}</div>
                        <div class="text-base font-semibold text-orange-400">Balance Due: {getCurrencySymbol(form.currency)}{money(balanceDue)}</div>
                    {/if}
                </CardFooter>
                <CardContent class="border-t border-white/10 pt-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <Checkbox bind:checked={form.paid_in_full} disabled={!isEditable} />
                        <div>
                            <span class="text-sm font-medium text-white">Payment made in full</span>
                            <p class="text-xs text-neutral-400 mt-0.5">Check this if the customer has paid the full amount. The receipt status will be set to "Paid".</p>
                        </div>
                    </label>
                    <InputError message={form.errors.paid_in_full} class="mt-2" />
                </CardContent>
            </Card>

            <div class="flex justify-end gap-3 -mt-2">
                {#if isEditable}
                    <Button type="submit" disabled={form.processing}>Save Receipt</Button>
                {/if}
            </div>
        </form>
    </div>
</AuthenticatedLayout>
