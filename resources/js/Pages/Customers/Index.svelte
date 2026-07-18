<script>
    import { Link, router } from '@inertiajs/svelte';
    import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.svelte';
    import { Card, CardContent } from '$lib/components/ui/card';
    import { Button } from '$lib/components/ui/button';
    import CustomerFormModal from '../../Components/CustomerFormModal.svelte';

    let { customers } = $props();

    let showCreateModal = $state(false);

    function destroy(customer) {
        if (confirm(`Delete ${customer.name}?`)) {
            router.delete(`/customers/${customer.id}`);
        }
    }
</script>

<svelte:head>
    <title>Customers</title>
</svelte:head>

<AuthenticatedLayout>
    {#snippet header()}
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">Customers</h2>
            <Button class="bg-blue-600 text-white shadow-none hover:bg-blue-500" onclick={() => (showCreateModal = true)}>New customer</Button>
        </div>
    {/snippet}

    <CustomerFormModal show={showCreateModal} onclose={() => (showCreateModal = false)} />

    <div class="space-y-6">
        <Card>
            <CardContent class="p-0">
                {#if customers.data.length === 0}
                    <div class="p-6 text-sm text-neutral-400">No customers yet. Add your first one to get started.</div>
                {:else}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-neutral-400 uppercase border-b border-white/10">
                                <tr>
                                    <th class="px-6 py-3">Name</th>
                                    <th class="px-6 py-3">Company</th>
                                    <th class="px-6 py-3">Email</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each customers.data as customer (customer.id)}
                                    <tr class="border-b border-white/10 last:border-0 hover:bg-white/5">
                                        <td class="px-6 py-4 font-medium text-white whitespace-nowrap">{customer.name}</td>
                                        <td class="px-6 py-4 text-neutral-400 whitespace-nowrap">{customer.company ?? '—'}</td>
                                        <td class="px-6 py-4 text-neutral-400 whitespace-nowrap">{customer.email ?? '—'}</td>
                                        <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                            <Link href={`/customers/${customer.id}/edit`} class="text-blue-400 hover:underline">Edit</Link>
                                            <button onclick={() => destroy(customer)} class="text-red-400 hover:underline">Delete</button>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                {/if}
            </CardContent>
        </Card>

        {#if customers.links.length > 3}
            <div class="flex gap-1 flex-wrap">
                {#each customers.links as link (link.label)}
                    {#if link.url}
                        <Link
                            href={link.url}
                            class={`px-3 py-1 rounded-md text-sm ${link.active ? 'bg-white/10 text-white' : 'bg-transparent border border-white/10 text-neutral-300 hover:bg-white/5'}`}
                        >
                            {@html link.label}
                        </Link>
                    {:else}
                        <span class="px-3 py-1 rounded-md text-sm text-neutral-600">{@html link.label}</span>
                    {/if}
                {/each}
            </div>
        {/if}
    </div>
</AuthenticatedLayout>
