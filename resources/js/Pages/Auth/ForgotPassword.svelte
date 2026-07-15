<script>
    import { useForm } from '@inertiajs/svelte';
    import GuestLayout from '../../Layouts/GuestLayout.svelte';
    import InputError from '../../Components/InputError.svelte';
    import InputLabel from '../../Components/InputLabel.svelte';
    import TextInput from '../../Components/TextInput.svelte';
    import PrimaryButton from '../../Components/PrimaryButton.svelte';

    let { status = undefined } = $props();

    const form = useForm({
        email: '',
    });

    function submit(e) {
        e.preventDefault();
        form.post('/forgot-password');
    }
</script>

<svelte:head>
    <title>Forgot Password</title>
</svelte:head>

<GuestLayout>
    <div class="mb-4 text-sm text-gray-600">
        Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
    </div>

    {#if status}
        <div class="mb-4 font-medium text-sm text-green-600">
            {status}
        </div>
    {/if}

    <form onsubmit={submit}>
        <div>
            <InputLabel for="email" value="Email" />
            <TextInput
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                bind:value={form.email}
                required
                autofocus
            />
            <InputError message={form.errors.email} class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <PrimaryButton disabled={form.processing}>
                Email Password Reset Link
            </PrimaryButton>
        </div>
    </form>
</GuestLayout>
