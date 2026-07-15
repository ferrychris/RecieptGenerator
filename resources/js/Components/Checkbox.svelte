<script>
    let { checked = $bindable(false), class: className = '', ...restProps } = $props();
</script>

<input
    type="checkbox"
    bind:checked
    class={`rounded border-gray-300 dark:border-white/10 bg-transparent text-orange-500 shadow-sm focus:ring-orange-500 focus:ring-offset-neutral-950 ${className}`}
    {...restProps}
/>
