<script>
    import { useForm } from '@inertiajs/svelte';
    import GuestLayout from '../../Layouts/GuestLayout.svelte';
    import InputError from '../../Components/InputError.svelte';
    import InputLabel from '../../Components/InputLabel.svelte';
    import TextInput from '../../Components/TextInput.svelte';
    import PrimaryButton from '../../Components/PrimaryButton.svelte';

    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    function submit(e) {
        e.preventDefault();
        form.post('/register', {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    }
</script>

<svelte:head>
    <title>Register</title>
</svelte:head>

<GuestLayout>
    <form onsubmit={submit}>
        <div>
            <InputLabel for="name" value="Name" />
            <TextInput
                id="name"
                class="block mt-1 w-full"
                type="text"
                name="name"
                bind:value={form.name}
                required
                autofocus
                autocomplete="name"
            />
            <InputError message={form.errors.name} class="mt-2" />
        </div>

        <div class="mt-4">
            <InputLabel for="email" value="Email" />
            <TextInput
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                bind:value={form.email}
                required
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

        <div class="flex items-center justify-end mt-6">
            <a
                class="underline text-sm text-neutral-400 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-neutral-950 focus:ring-orange-500 transition-colors"
                href="/login"
            >
                Already registered?
            </a>

            <PrimaryButton class="ms-4" disabled={form.processing}>
                Register
            </PrimaryButton>
        </div>
    </form>
</GuestLayout>
