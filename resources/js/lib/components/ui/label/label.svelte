<script>
    import { Label as LabelPrimitive } from 'bits-ui';
    import { cn } from '$lib/utils.js';

    let { class: className, ...restProps } = $props();
</script>

<LabelPrimitive.Root
    class={cn(
        'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70',
        className,
    )}
    {...restProps}
/>
