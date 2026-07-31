<script>
    import { Link } from '@inertiajs/svelte';
    import ApplicationLogo from '../../Components/ApplicationLogo.svelte';
    import { ShieldCheck } from '@lucide/svelte';

    let { receipt } = $props();

    const statusStyles = {
        unpaid: 'bg-red-500/10 text-red-400 ring-red-500/20',
        part_payment: 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20',
        paid: 'bg-green-500/10 text-green-400 ring-green-500/20',
    };

    function formatStatus(status) {
        return status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
    }
</script>

<svelte:head>
    <title>Verify receipt {receipt.number}</title>
</svelte:head>

<div class="dark min-h-screen w-full flex items-center justify-center bg-neutral-950 text-white p-4">
    <div class="w-full max-w-md">
        <div class="mb-6 flex justify-center">
            <ApplicationLogo class="h-8 w-auto fill-current text-white" />
        </div>

        <div class="rounded-2xl border border-white/10 bg-neutral-900 shadow-2xl overflow-hidden">
            <div class="flex flex-col items-center gap-3 border-b border-white/10 bg-green-500/10 px-6 py-8 text-center">
                <span class="flex size-12 items-center justify-center rounded-full bg-green-500/15 text-green-400">
                    <ShieldCheck class="size-7" strokeWidth={1.75} />
                </span>
                <div>
                    <div class="text-base font-semibold text-white">Verified authentic receipt</div>
                    <p class="mt-1 text-sm text-neutral-400">Issued by {receipt.business_name}</p>
                </div>
            </div>

            <div class="px-6 py-6 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-400">Receipt number</span>
                    <span class="text-sm font-medium text-white">{receipt.number}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-400">Date</span>
                    <span class="text-sm font-medium text-white">{receipt.issue_date}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-400">Billed to</span>
                    <span class="text-sm font-medium text-white">{receipt.customer_name}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-400">Status</span>
                    <span class={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium ring-1 ${statusStyles[receipt.status]}`}>
                        {formatStatus(receipt.status)}
                    </span>
                </div>

                <div class="border-t border-white/10 pt-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-neutral-400">Total</span>
                        <span class="text-base font-semibold text-white">{receipt.total}</span>
                    </div>
                    {#if receipt.amount_paid}
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-neutral-400">Amount paid</span>
                            <span class="text-sm text-white">{receipt.amount_paid}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-neutral-400">Balance due</span>
                            <span class="text-sm text-white">{receipt.balance_due}</span>
                        </div>
                    {/if}
                </div>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-neutral-500">
            Verified against ReceiptGen's records. This page can only be reached with a valid, unaltered link.
        </p>
        <p class="mt-2 text-center text-xs text-neutral-600">
            <Link href="/" class="hover:text-neutral-400 hover:underline">Powered by ReceiptGen</Link>
        </p>
    </div>
</div>
