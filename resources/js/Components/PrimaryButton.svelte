<script>
    let { type = 'submit', class: className = '', children, ...restProps } = $props();
</script>

<button
    {type}
    class={`inline-flex items-center justify-center px-4 py-2 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-neutral-950 transition ease-in-out duration-150 disabled:opacity-50 disabled:pointer-events-none ${className}`}
    {...restProps}
>
    {@render children?.()}
</button>
