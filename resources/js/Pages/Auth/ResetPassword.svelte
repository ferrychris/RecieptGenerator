<script>
    import { useForm } from '@inertiajs/svelte';
    import GuestLayout from '../../Layouts/GuestLayout.svelte';
    import InputError from '../../Components/InputError.svelte';
    import InputLabel from '../../Components/InputLabel.svelte';
    import TextInput from '../../Components/TextInput.svelte';
    import PrimaryButton from '../../Components/PrimaryButton.svelte';

    let { email = '', token = '' } = $props();

    const form = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    function submit(e) {
        e.preventDefault();
        form.post('/reset-password', {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    }
</script>

<svelte:head>
    <title>Reset Password</title>
</svelte:head>

<GuestLayout>
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
                autocomplete="username"
            />
            <InputError message={form.errors.email} class="mt-2" />
        </div>

        <div class="mt-4">
            <InputLabel for="password" value="Password" />
            <TextInput
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                bind:value={form.password}
                required
                autocomplete="new-password"
            />
            <InputError message={form.errors.password} class="mt-2" />
        </div>

        <div class="mt-4">
            <InputLabel for="password_confirmation" value="Confirm Password" />
            <TextInput
                id="password_confirmation"
                class="block mt-1 w-full"
                type="password"
                name="password_confirmation"
                bind:value={form.password_confirmation}
                required
                autocomplete="new-password"
            />
            <InputError message={form.errors.password_confirmation} class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <PrimaryButton disabled={form.processing}>
                Reset Password
            </PrimaryButton>
        </div>
    </form>
</GuestLayout>
