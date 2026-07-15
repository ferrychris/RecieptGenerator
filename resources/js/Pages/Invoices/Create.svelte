<script>
    import { useForm } from '@inertiajs/svelte';
    import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.svelte';
    import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '$lib/components/ui/card';
    import { Label } from '$lib/components/ui/label';
    import { Input } from '$lib/components/ui/input';
    import { Button } from '$lib/components/ui/button';
    import InputError from '../../Components/InputError.svelte';
    import CustomerFormModal from '../../Components/CustomerFormModal.svelte';
    import CustomerSelect from '../../Components/CustomerSelect.svelte';
    import Checkbox from '../../Components/Checkbox.svelte';

    let { customers: initialCustomers, defaultCurrency } = $props();

    let customers = $state(initialCustomers);
    let showCreateCustomerModal = $state(false);

    function today() {
        return new Date().toISOString().slice(0, 10);
    }

    function emptyItem() {
        return { description: '', qty: 1, unit_price: 0, tax_rate: 0 };
    }

    const form = useForm({
        customer_id: '',
        currency: defaultCurrency ?? 'USD',
        issue_date: today(),
        notes: '',
        paid_in_full: false,
        items: [emptyItem()],
    });

    function onCustomerCreated(customer) {
        customers = [...customers, customer].sort((a, b) => a.name.localeCompare(b.name));
        form.customer_id = customer.id;
        showCreateCustomerModal = false;
    }

    function addItem() {
        form.items = [...form.items, emptyItem()];
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
        form.post('/invoices');
    }
</script>

<svelte:head>
    <title>New receipt</title>
</svelte:head>

<AuthenticatedLayout>
    {#snippet header()}
        <h2 class="font-semibold text-xl text-white leading-tight">New receipt</h2>
    {/snippet}

    <div>
        <form onsubmit={submit} class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Details</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <Label for="customer_id">Customer</Label>
                            <button
                                type="button"
                                class="text-xs text-blue-400 hover:underline"
                                onclick={() => (showCreateCustomerModal = true)}
                            >
                                + New customer
                            </button>
                        </div>
                        <CustomerSelect {customers} bind:value={form.customer_id} id="customer_id" />
                        <InputError message={form.errors.customer_id} class="mt-2" />
                        {#if customers.length === 0}
                            <p class="text-sm text-neutral-400 mt-2">No customers yet — use "+ New customer" above to add one.</p>
                        {/if}
                    </div>



                    <div>
                        <Label for="issue_date">Date</Label>
                        <Input id="issue_date" type="date" class="mt-1" bind:value={form.issue_date} required />
                        <InputError message={form.errors.issue_date} class="mt-2" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Line items</CardTitle>
                    <CardDescription>Add each product or service being paid for.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    {#each form.items as item, index (index)}
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start border-b border-white/10 pb-4 last:border-0 last:pb-0">
                            <div class="sm:col-span-5">
                                <Label for={`item-desc-${index}`}>Description</Label>
                                <Input id={`item-desc-${index}`} class="mt-1" bind:value={item.description} required />
                                <InputError message={form.errors[`items.${index}.description`]} class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <Label for={`item-qty-${index}`}>Qty</Label>
                                <Input id={`item-qty-${index}`} type="number" step="0.01" min="0.01" class="mt-1" bind:value={item.qty} required />
                            </div>
                            <div class="sm:col-span-2">
                                <Label for={`item-price-${index}`}>Unit price</Label>
                                <Input id={`item-price-${index}`} type="number" step="0.01" min="0" class="mt-1" bind:value={item.unit_price} required />
                            </div>
                            <div class="sm:col-span-2">
                                <Label for={`item-tax-${index}`}>Tax %</Label>
                                <Input id={`item-tax-${index}`} type="number" step="0.01" min="0" max="100" class="mt-1" bind:value={item.tax_rate} />
                            </div>
                            <div class="sm:col-span-1 flex sm:justify-end sm:pt-6">
                                <Button type="button" variant="ghost" size="sm" onclick={() => removeItem(index)} disabled={form.items.length <= 1}>
                                    Remove
                                </Button>
                            </div>
                        </div>
                    {/each}

                    <Button type="button" variant="outline" onclick={addItem}>Add line item</Button>
                </CardContent>
                <CardFooter class="flex flex-col items-end gap-1 border-t border-white/10 pt-4">
                    <div class="text-sm text-neutral-400">Subtotal: {getCurrencySymbol(form.currency)}{money(subtotal)}</div>
                    <div class="text-sm text-neutral-400">Tax: {getCurrencySymbol(form.currency)}{money(taxTotal)}</div>
                    <div class="text-base font-semibold text-white">Total: {getCurrencySymbol(form.currency)}{money(total)}</div>
                </CardFooter>
                <CardContent class="pt-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <Checkbox bind:checked={form.paid_in_full} />
                        <div>
                            <span class="text-sm font-medium text-white">Payment made in full</span>
                            <p class="text-xs text-neutral-400 mt-0.5">Check this if the customer has already paid the full amount. The receipt will be marked as "Paid".</p>
                        </div>
                    </label>
                    <InputError message={form.errors.paid_in_full} class="mt-2" />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Notes</CardTitle>
                </CardHeader>
                <CardContent>
                    <Input id="notes" bind:value={form.notes} />
                </CardContent>
            </Card>

            <div class="flex justify-end">
                <Button type="submit" disabled={form.processing}>Generate Receipt</Button>
            </div>
        </form>
    </div>

    <CustomerFormModal
        show={showCreateCustomerModal}
        onclose={() => (showCreateCustomerModal = false)}
        onCreated={onCustomerCreated}
    />
</AuthenticatedLayout>
