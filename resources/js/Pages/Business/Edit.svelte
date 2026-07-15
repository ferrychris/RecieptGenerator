<script>
    import { useForm } from '@inertiajs/svelte';
    import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.svelte';
    import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '$lib/components/ui/card';
    import { Label } from '$lib/components/ui/label';
    import { Input } from '$lib/components/ui/input';
    import { Button } from '$lib/components/ui/button';
    import InputError from '../../Components/InputError.svelte';
    import TemplateSelector from '../../Components/TemplateSelector.svelte';
    import Checkbox from '../../Components/Checkbox.svelte';

    let { business } = $props();

    const form = useForm({
        name: business.name ?? '',
        address: business.address ?? '',
        phone: business.phone ?? '',
        email: business.email ?? '',
        tax_id: business.tax_id ?? '',
        default_currency: business.default_currency ?? 'USD',
        template: business.template ?? 'ledger',
        tagline: business.tagline ?? '',
        show_name_on_receipt: business.show_name_on_receipt ?? true,
        logo: null,
    });

    function submit(e) {
        e.preventDefault();
        form.transform((data) => ({
            ...data,
            _method: 'patch',
        })).post('/business/settings', {
            preserveScroll: true,
            forceFormData: true,
        });
    }
</script>

<svelte:head>
    <title>Business settings</title>
</svelte:head>

<AuthenticatedLayout>
    {#snippet header()}
        <h2 class="font-semibold text-xl text-white leading-tight">Business settings</h2>
    {/snippet}

    <div class="w-full">
            <Card>
                <CardHeader>
                    <CardTitle>Business profile</CardTitle>
                    <CardDescription>This information appears on your receipts.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form onsubmit={submit} class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <Label for="name">Business name</Label>
                                <Input id="name" class="mt-1" bind:value={form.name} required />
                                <InputError message={form.errors.name} class="mt-2" />
                            </div>

                            <div>
                                <Label for="logo">Logo</Label>
                                {#if business.logo_full_url}
                                    <div class="mt-2 mb-2">
                                        <img src={business.logo_full_url} alt="Business Logo" class="h-16 w-auto object-contain border rounded bg-neutral-50 p-1" />
                                    </div>
                                {/if}
                                <Input id="logo" type="file" accept="image/*" class="mt-1" onchange={(e) => form.logo = e.target.files[0]} />
                                <InputError message={form.errors.logo} class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <Label for="tagline">Tagline</Label>
                                <Input id="tagline" class="mt-1" placeholder="e.g. Quality you can trust" bind:value={form.tagline} />
                                <InputError message={form.errors.tagline} class="mt-2" />
                            </div>

                            <div>
                                <Label for="tax_id">Tax ID</Label>
                                <Input id="tax_id" class="mt-1" bind:value={form.tax_id} />
                                <InputError message={form.errors.tax_id} class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <Label for="address">Address</Label>
                            <Input id="address" class="mt-1" bind:value={form.address} />
                            <InputError message={form.errors.address} class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <Label for="phone">Phone</Label>
                                <Input id="phone" class="mt-1" bind:value={form.phone} />
                                <InputError message={form.errors.phone} class="mt-2" />
                            </div>
                            <div>
                                <Label for="email">Contact email</Label>
                                <Input id="email" type="email" class="mt-1" placeholder="Defaults to your account email" bind:value={form.email} />
                                <InputError message={form.errors.email} class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <Label for="default_currency">Default currency</Label>
                            <select
                                id="default_currency"
                                bind:value={form.default_currency}
                                required
                                class="mt-1 flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            >
                                <option value="USD">USD ($)</option>
                                <option value="NGN">NGN (₦)</option>
                                <option value="GBP">GBP (£)</option>
                                <option value="EUR">EUR (€)</option>
                            </select>
                            <InputError message={form.errors.default_currency} class="mt-2" />
                        </div>

                        <div>
                            <Label class="mb-3 block">Receipt Template</Label>
                            <TemplateSelector bind:value={form.template} />
                            <InputError message={form.errors.template} class="mt-2" />
                        </div>

                        <label class="flex items-center gap-2">
                            <Checkbox bind:checked={form.show_name_on_receipt} />
                            <span class="text-sm text-white">Show business name on receipts</span>
                        </label>
                        <p class="text-xs text-neutral-400 -mt-2">Turn this off if your logo already includes your business name.</p>
                        <InputError message={form.errors.show_name_on_receipt} class="mt-2" />

                        <Button type="submit" disabled={form.processing}>Save</Button>
                    </form>
                </CardContent>
            </Card>
    </div>
</AuthenticatedLayout>
