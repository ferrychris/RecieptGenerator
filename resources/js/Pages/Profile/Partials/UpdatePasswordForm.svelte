<script>
    import { useForm, usePage } from '@inertiajs/svelte';
    import InputError from '../../../Components/InputError.svelte';
    import InputLabel from '../../../Components/InputLabel.svelte';
    import TextInput from '../../../Components/TextInput.svelte';
    import PrimaryButton from '../../../Components/PrimaryButton.svelte';

    const page = usePage();

    let currentPasswordInput = $state(null);
    let passwordInput = $state(null);

    const form = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    function submit(e) {
        e.preventDefault();
        form.put('/password', {
            preserveScroll: true,
            errorBag: 'updatePassword',
            onSuccess: () => form.reset(),
            onError: () => {
                if (form.errors.password) {
                    form.reset('password', 'password_confirmation');
                    passwordInput?.focus();
                }
                if (form.errors.current_password) {
                    form.reset('current_password');
                    currentPasswordInput?.focus();
                }
            },
        });
    }

    const status = $derived(page.props.status);
    let showSaved = $state(false);
    $effect(() => {
        if (status === 'password-updated') {
            showSaved = true;
            const timeout = setTimeout(() => (showSaved = false), 2000);
            return () => clearTimeout(timeout);
        }
    });
</script>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Update Password</h2>

        <p class="mt-1 text-sm text-gray-600">
            Ensure your account is using a long, random password to stay secure.
        </p>
    </header>

    <form onsubmit={submit} class="mt-6 space-y-6">
        <div>
            <InputLabel for="update_password_current_password" value="Current Password" />
            <TextInput
                bind:ref={currentPasswordInput}
                id="update_password_current_password"
                name="current_password"
                type="password"
                class="mt-1 block w-full"
                bind:value={form.current_password}
                autocomplete="current-password"
            />
            <InputError message={form.errors.current_password} class="mt-2" />
        </div>

        <div>
            <InputLabel for="update_password_password" value="New Password" />
            <TextInput
                bind:ref={passwordInput}
                id="update_password_password"
                name="password"
                type="password"
                class="mt-1 block w-full"
                bind:value={form.password}
                autocomplete="new-password"
            />
            <InputError message={form.errors.password} class="mt-2" />
        </div>

        <div>
            <InputLabel for="update_password_password_confirmation" value="Confirm Password" />
            <TextInput
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                class="mt-1 block w-full"
                bind:value={form.password_confirmation}
                autocomplete="new-password"
            />
            <InputError message={form.errors.password_confirmation} class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton disabled={form.processing}>Save</PrimaryButton>

            {#if showSaved}
                <p class="text-sm text-gray-600">Saved.</p>
            {/if}
        </div>
    </form>
</section>
