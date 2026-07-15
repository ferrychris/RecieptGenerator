<script>
    import { useForm } from '@inertiajs/svelte';
    import GuestLayout from '../../Layouts/GuestLayout.svelte';
    import InputError from '../../Components/InputError.svelte';
    import InputLabel from '../../Components/InputLabel.svelte';
    import TextInput from '../../Components/TextInput.svelte';
    import PrimaryButton from '../../Components/PrimaryButton.svelte';

    const form = useForm({
        password: '',
    });

    function submit(e) {
        e.preventDefault();
        form.post('/confirm-password', {
            onFinish: () => form.reset(),
        });
    }
</script>

<svelte:head>
    <title>Confirm Password</title>
</svelte:head>

<GuestLayout>
    <div class="mb-4 text-sm text-gray-600">
        This is a secure area of the application. Please confirm your password before continuing.
    </div>

    <form onsubmit={submit}>
        <div>
            <InputLabel for="password" value="Password" />

            <TextInput
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                bind:value={form.password}
                required
                autofocus
                autocomplete="current-password"
            />

            <InputError message={form.errors.password} class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <PrimaryButton disabled={form.processing}>
                Confirm
            </PrimaryButton>
        </div>
    </form>
</GuestLayout>
