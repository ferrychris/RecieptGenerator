<script>
    import { Link } from '@inertiajs/svelte';
    import ApplicationLogo from '../Components/ApplicationLogo.svelte';

    let { children } = $props();
</script>

<div class="dark relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-neutral-950 text-white antialiased overflow-hidden">
    <!-- ambient backdrop: radial glow -->
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute left-1/2 top-[-200px] h-[500px] w-[700px] -translate-x-1/2 rounded-full bg-orange-500/10 blur-[100px]"></div>
    </div>

    <div class="relative z-10">
        <Link href="/">
            <ApplicationLogo class="h-14 w-auto fill-current text-white" />
        </Link>
    </div>

    <div class="relative z-10 w-full sm:max-w-md mt-6 px-8 py-8 bg-neutral-900/60 border border-white/10 shadow-2xl backdrop-blur-md overflow-hidden sm:rounded-2xl">
        {@render children?.()}
    </div>
</div>
