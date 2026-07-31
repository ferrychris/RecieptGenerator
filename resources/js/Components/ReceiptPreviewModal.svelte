<script>
    import Modal from './Modal.svelte';
    import { Button } from '$lib/components/ui/button';
    import { X, Download, ExternalLink } from '@lucide/svelte';

    let { invoice = null, onclose = () => {} } = $props();

    let loading = $state(true);

    // Re-arm the spinner whenever a different receipt is opened, otherwise the
    // second preview shows the previous PDF while the new one is still rendering.
    $effect(() => {
        invoice?.id;
        loading = true;
    });
</script>

<Modal show={!!invoice} maxWidth="4xl" {onclose}>
    {#if invoice}
        <div class="flex items-center justify-between border-b border-white/10 px-5 py-3">
            <div class="min-w-0">
                <h2 class="truncate text-sm font-semibold text-white">Receipt {invoice.number}</h2>
                {#if invoice.customer?.name}
                    <p class="truncate text-xs text-neutral-400">{invoice.customer.name}</p>
                {/if}
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <a href={`/invoices/${invoice.id}/pdf`} target="_blank" rel="noopener">
                    <Button variant="outline" size="sm">
                        <Download class="mr-2 size-4" />
                        <span class="hidden sm:inline">Download</span>
                    </Button>
                </a>
                <!-- Escape hatch: browsers without an inline PDF viewer (most
                     mobile ones) render an empty frame, so keep a way out. -->
                <a href={`/invoices/${invoice.id}/preview`} target="_blank" rel="noopener">
                    <Button variant="outline" size="sm" title="Open in new tab">
                        <ExternalLink class="size-4" />
                    </Button>
                </a>
                <Button variant="ghost" size="sm" onclick={onclose} title="Close">
                    <X class="size-4" />
                </Button>
            </div>
        </div>

        <div class="relative bg-neutral-950" style="height: min(80vh, 900px);">
            {#if loading}
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-3">
                    <div class="size-8 animate-spin rounded-full border-2 border-white/15 border-t-white"></div>
                    <p class="text-sm text-neutral-400">Generating receipt…</p>
                </div>
            {/if}

            <!-- Keyed on id so switching receipts forces a fresh request rather
                 than reusing the cached frame. -->
            {#key invoice.id}
                <iframe
                    src={`/invoices/${invoice.id}/preview`}
                    title={`Receipt ${invoice.number}`}
                    class="size-full"
                    onload={() => (loading = false)}
                ></iframe>
            {/key}
        </div>
    {/if}
</Modal>
