<script>
    let {
        value = $bindable(),
        class: className = '',
        type = 'text',
        autofocus = false,
        ref = $bindable(null),
        ...restProps
    } = $props();

    $effect(() => {
        if (autofocus && ref) {
            ref.focus();
        }
    });
</script>

<input
    bind:this={ref}
    bind:value
    {type}
    class={`border-gray-300 dark:border-white/10 dark:bg-neutral-950 dark:text-white dark:focus:border-orange-500 dark:focus:ring-orange-500 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm transition-colors ${className}`}
    {...restProps}
/>
