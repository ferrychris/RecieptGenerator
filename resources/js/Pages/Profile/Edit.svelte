<script>
    import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.svelte';
    import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.svelte';
    import UpdatePasswordForm from './Partials/UpdatePasswordForm.svelte';
    import DeleteUserForm from './Partials/DeleteUserForm.svelte';

    let { mustVerifyEmail = false, status = undefined } = $props();
</script>

<svelte:head>
    <title>Profile</title>
</svelte:head>

<AuthenticatedLayout>
    {#snippet header()}
        <h2 class="font-semibold text-xl text-white leading-tight">Profile</h2>
    {/snippet}

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <UpdateProfileInformationForm {mustVerifyEmail} {status} />
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <UpdatePasswordForm />
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </div>
</AuthenticatedLayout>
