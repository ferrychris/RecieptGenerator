<script>
    let { message = undefined, class: className = '' } = $props();
</script>

{#if message}
    <p class={`text-sm text-red-600 ${className}`}>
        {message}
    </p>
{/if}
