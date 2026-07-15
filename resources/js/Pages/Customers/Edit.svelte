<script>
    import { useForm } from '@inertiajs/svelte';
    import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.svelte';
    import { Card, CardContent } from '$lib/components/ui/card';
    import { Label } from '$lib/components/ui/label';
    import { Input } from '$lib/components/ui/input';
    import { Button } from '$lib/components/ui/button';
    import InputError from '../../Components/InputError.svelte';

    let { customer } = $props();

    const form = useForm({
        name: customer.name ?? '',
        company: customer.company ?? '',
        email: customer.email ?? '',
        whatsapp_number: customer.whatsapp_number ?? '',
        billing_address: customer.billing_address ?? '',
    });

    function submit(e) {
        e.preventDefault();
        form.patch(`/customers/${customer.id}`);
    }
</script>

<svelte:head>
    <title>Edit customer</title>
</svelte:head>

<AuthenticatedLayout>
    {#snippet header()}
        <h2 class="font-semibold text-xl text-white leading-tight">Edit customer</h2>
    {/snippet}

    <div class="max-w-3xl">
            <Card>
                <CardContent class="pt-6">
                    <form onsubmit={submit} class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <Label for="name">Name</Label>
                                <Input id="name" class="mt-1" bind:value={form.name} required autofocus />
                                <InputError message={form.errors.name} class="mt-2" />
                            </div>
                            <div>
                                <Label for="company">Company</Label>
                                <Input id="company" class="mt-1" bind:value={form.company} />
                                <InputError message={form.errors.company} class="mt-2" />
                            </div>
                            <div>
                                <Label for="email">Email</Label>
                                <Input id="email" type="email" class="mt-1" bind:value={form.email} />
                                <InputError message={form.errors.email} class="mt-2" />
                            </div>
                            <div>
                                <Label for="whatsapp_number">WhatsApp number</Label>
                                <Input id="whatsapp_number" class="mt-1" bind:value={form.whatsapp_number} />
                                <InputError message={form.errors.whatsapp_number} class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <Label for="billing_address">Billing address</Label>
                            <Input id="billing_address" class="mt-1" bind:value={form.billing_address} />
                            <InputError message={form.errors.billing_address} class="mt-2" />
                        </div>

                        <div class="flex gap-2">
                            <Button type="submit" disabled={form.processing}>Save changes</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
    </div>
</AuthenticatedLayout>
