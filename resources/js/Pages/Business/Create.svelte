<script>
    import { useForm, Link, usePage } from '@inertiajs/svelte';
    import ApplicationLogo from '../../Components/ApplicationLogo.svelte';
    import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '$lib/components/ui/card';
    import { Label } from '$lib/components/ui/label';
    import { Input } from '$lib/components/ui/input';
    import { Button } from '$lib/components/ui/button';
    import InputError from '../../Components/InputError.svelte';
    import TemplateSelector from '../../Components/TemplateSelector.svelte';
    import { Toaster, toast } from 'svelte-sonner';

    const page = usePage();
    const hasExistingOrganizations = $derived((page.props.organizations ?? []).length > 0);
    const flash = $derived(page.props.flash);

    $effect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
        if (flash?.message) toast(flash.message);
    });

    let step = $state(1);

    const form = useForm({
        name: '',
        address: '',
        phone: '',
        email: '',
        tax_id: '',
        default_currency: 'USD',
        template: 'ledger',
        tagline: '',
        logo: null,
    });

    const currencies = [
        'USD', 'EUR', 'GBP', 'AUD', 'CAD', 'JPY', 'INR', 'NGN', 'KES', 'ZAR', 'GHS', 'UGX', 'RWF'
    ];

    function nextStep() {
        step = 2;
    }

    function prevStep() {
        step = 1;
    }

    function submit(e) {
        e.preventDefault();
        form.post('/business', {
            forceFormData: true,
        });
    }
</script>

<svelte:head>
    <title>Set up your business</title>
</svelte:head>

<div class="dark min-h-screen w-full flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <Toaster theme="dark" position="top-right" richColors />
    <div class="fixed inset-0 bg-neutral-950/80 -z-10"></div>

    <div class="w-full max-w-lg z-10">
        <div class="mb-6 flex justify-center">
            <ApplicationLogo class="h-14 w-auto fill-current text-white" />
        </div>

        <Card class="w-full border-white/10 bg-neutral-900 text-white shadow-2xl relative">
            <CardHeader>
                <CardTitle>Set up your business</CardTitle>
                <CardDescription class="text-neutral-400">
                    {step === 1 ? 'Step 1 of 2: Basic Information' : 'Step 2 of 2: Contact & Template'}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form onsubmit={submit} class="space-y-4">
                    {#if step === 1}
                        <div>
                            <Label for="name" class="text-neutral-200">Business name</Label>
                            <Input id="name" class="mt-1 bg-neutral-950 border-white/10 text-white focus:border-indigo-500" bind:value={form.name} required autofocus />
                            <InputError message={form.errors.name} class="mt-2" />
                        </div>

                        <div>
                            <Label for="tagline" class="text-neutral-200">Tagline</Label>
                            <Input id="tagline" class="mt-1 bg-neutral-950 border-white/10 text-white placeholder-neutral-500 focus:border-indigo-500" placeholder="e.g. Quality you can trust" bind:value={form.tagline} />
                            <InputError message={form.errors.tagline} class="mt-2" />
                        </div>

                        <div>
                            <Label for="logo" class="text-neutral-200">Logo</Label>
                            <Input id="logo" type="file" accept="image/*" class="mt-1 bg-neutral-950 border-white/10 text-white file:text-neutral-200 file:bg-neutral-800 hover:file:bg-neutral-700" onchange={(e) => form.logo = e.target.files[0]} />
                            <InputError message={form.errors.logo} class="mt-2" />
                        </div>

                        <div>
                            <Label for="default_currency" class="text-neutral-200">Default currency</Label>
                            <select 
                                id="default_currency" 
                                class="mt-1 block w-full rounded-md border border-white/10 bg-neutral-950 text-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none transition-colors" 
                                bind:value={form.default_currency} 
                                required
                            >
                                {#each currencies as currency}
                                    <option value={currency}>{currency}</option>
                                {/each}
                            </select>
                            <InputError message={form.errors.default_currency} class="mt-2" />
                        </div>

                        <div class="pt-4">
                            <Button type="button" class="w-full text-white" onclick={nextStep}>Next step</Button>
                        </div>
                    {/if}

                    {#if step === 2}
                        <div>
                            <Label for="address" class="text-neutral-200">Address</Label>
                            <Input id="address" class="mt-1 bg-neutral-950 border-white/10 text-white focus:border-indigo-500" bind:value={form.address} />
                            <InputError message={form.errors.address} class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <Label for="phone" class="text-neutral-200">Phone</Label>
                                <Input id="phone" class="mt-1 bg-neutral-950 border-white/10 text-white focus:border-indigo-500" bind:value={form.phone} />
                                <InputError message={form.errors.phone} class="mt-2" />
                            </div>
                            <div>
                                <Label for="email" class="text-neutral-200">Contact email</Label>
                                <Input id="email" type="email" class="mt-1 bg-neutral-950 border-white/10 text-white focus:border-indigo-500" bind:value={form.email} />
                                <InputError message={form.errors.email} class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <Label for="tax_id" class="text-neutral-200">Tax ID</Label>
                            <Input id="tax_id" class="mt-1 bg-neutral-950 border-white/10 text-white focus:border-indigo-500" bind:value={form.tax_id} />
                            <InputError message={form.errors.tax_id} class="mt-2" />
                        </div>

                        <div>
                            <Label class="mb-3 block text-neutral-200">Receipt Template</Label>
                            <TemplateSelector bind:value={form.template} />
                            <InputError message={form.errors.template} class="mt-2" />
                        </div>

                        <div class="pt-4 flex gap-3">
                            <Button type="button" variant="outline" class="w-1/3 bg-transparent border-white/10 text-white hover:bg-white/10 hover:text-white" onclick={prevStep}>Back</Button>
                            <Button type="submit" class="w-2/3 text-white" disabled={form.processing}>Complete setup</Button>
                        </div>
                    {/if}

                    {#if hasExistingOrganizations}
                        <Link href="/dashboard" class="block text-center text-sm text-neutral-400 hover:text-white hover:underline mt-4">
                            Cancel
                        </Link>
                    {/if}
                </form>
            </CardContent>
        </Card>
    </div>
</div>
