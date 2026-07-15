<script>
    import { useForm } from '@inertiajs/svelte';
    import Modal from './Modal.svelte';
    import InputError from './InputError.svelte';
    import { Label } from '$lib/components/ui/label';
    import { Input } from '$lib/components/ui/input';
    import { Button } from '$lib/components/ui/button';

    // Pass `onCreated` to run in "quick create" mode: the customer is created
    // via a plain JSON fetch (no Inertia visit), so the host page never
    // navigates away and keeps whatever local state it has (e.g. an
    // in-progress invoice builder). Omit it to use normal Inertia page-mode
    // submission, which redirects back to the customers index on success.
    let { show = false, onclose = () => {}, onCreated = null } = $props();

    const form = useForm({
        name: '',
        company: '',
        email: '',
        whatsapp_number: '',
        billing_address: '',
    });

    function getXsrfToken() {
        const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        return match ? decodeURIComponent(match[1]) : '';
    }

    async function submitQuick() {
        form.processing = true;
        form.clearErrors();

        try {
            const response = await fetch('/customers', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': getXsrfToken(),
                },
                body: JSON.stringify({
                    name: form.name,
                    company: form.company,
                    email: form.email,
                    whatsapp_number: form.whatsapp_number,
                    billing_address: form.billing_address,
                }),
            });

            if (response.status === 422) {
                const body = await response.json();
                for (const [field, messages] of Object.entries(body.errors ?? {})) {
                    form.setError(field, messages[0]);
                }
                return;
            }

            if (!response.ok) {
                throw new Error(`Unexpected response: ${response.status}`);
            }

            const body = await response.json();
            form.reset();
            onCreated(body.customer);
        } finally {
            form.processing = false;
        }
    }

    function submit(e) {
        e.preventDefault();

        if (onCreated) {
            submitQuick();
            return;
        }

        form.post('/customers', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onclose();
            },
        });
    }

    function close() {
        form.clearErrors();
        form.reset();
        onclose();
    }
</script>

<Modal {show} onclose={close} maxWidth="lg">
    <form onsubmit={submit} class="p-6 space-y-4">
        <h2 class="text-lg font-semibold text-white">New customer</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <Label for="modal-name" class="text-neutral-300">Name</Label>
                <Input id="modal-name" class="mt-1 bg-neutral-950 border-white/10 text-white focus-visible:ring-orange-500" bind:value={form.name} required autofocus />
                <InputError message={form.errors.name} class="mt-2" />
            </div>
            <div>
                <Label for="modal-company" class="text-neutral-300">Company</Label>
                <Input id="modal-company" class="mt-1 bg-neutral-950 border-white/10 text-white focus-visible:ring-orange-500" bind:value={form.company} />
                <InputError message={form.errors.company} class="mt-2" />
            </div>
            <div>
                <Label for="modal-email" class="text-neutral-300">Email</Label>
                <Input id="modal-email" type="email" class="mt-1 bg-neutral-950 border-white/10 text-white focus-visible:ring-orange-500" bind:value={form.email} />
                <InputError message={form.errors.email} class="mt-2" />
            </div>
            <div>
                <Label for="modal-whatsapp" class="text-neutral-300">WhatsApp number</Label>
                <Input id="modal-whatsapp" class="mt-1 bg-neutral-950 border-white/10 text-white focus-visible:ring-orange-500" bind:value={form.whatsapp_number} />
                <InputError message={form.errors.whatsapp_number} class="mt-2" />
            </div>
        </div>

        <div>
            <Label for="modal-address" class="text-neutral-300">Billing address</Label>
            <Input id="modal-address" class="mt-1 bg-neutral-950 border-white/10 text-white focus-visible:ring-orange-500" bind:value={form.billing_address} />
            <InputError message={form.errors.billing_address} class="mt-2" />
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <Button type="button" variant="outline" class="border-white/10 hover:bg-white/5 hover:text-white text-neutral-300 transition-colors" onclick={close}>Cancel</Button>
            <Button type="submit" disabled={form.processing} class="bg-orange-500 hover:bg-orange-600 text-white font-semibold">Save customer</Button>
        </div>
    </form>
</Modal>
