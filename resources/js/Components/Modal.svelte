<script>
    let {
        show = false,
        maxWidth = '2xl',
        closeable = true,
        onclose = () => {},
        children,
    } = $props();

    const maxWidthClass = {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[maxWidth];

    function close() {
        if (closeable) {
            onclose();
        }
    }

    function handleKeydown(event) {
        if (event.key === 'Escape' && show) {
            close();
        }
    }

    $effect(() => {
        if (show) {
            document.body.classList.add('overflow-y-hidden');
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    });
</script>

<svelte:window onkeydown={handleKeydown} />

{#if show}
    <div class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
        <div
            class="fixed inset-0 transform transition-all"
            role="button"
            tabindex="-1"
            onclick={close}
        >
            <div class="absolute inset-0 bg-neutral-950/80 backdrop-blur-sm"></div>
        </div>

        <div
            class={`relative z-10 mb-6 bg-neutral-900 border border-white/10 text-white rounded-xl overflow-hidden shadow-2xl transform transition-all sm:w-full ${maxWidthClass} sm:mx-auto`}
        >
            {@render children?.()}
        </div>
    </div>
{/if}
