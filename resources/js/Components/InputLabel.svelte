<script>
    let { value = undefined, class: className = '', children, ...restProps } = $props();
</script>

<label {...restProps} class={`block font-medium text-sm text-gray-700 dark:text-neutral-300 ${className}`}>
    {#if value}
        {value}
    {:else if children}
        {@render children()}
    {/if}
</label>
