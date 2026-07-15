<script>
    import { cn } from '../../lib/utils';
    import { ArrowUpRight, Mail, Star } from '@lucide/svelte';

    let {
        badge = null,
        title,
        description = null,
        ratings = [],
        showEmail = true,
        emailLabel = 'Enter email address',
        emailPlaceholder = 'you@example.com',
        onSubmit = null,
        primaryCta = null,
        secondaryCta = null,
        avatars = [],
        socialProof = null,
        tabs = [],
        logos = [],
        class: className = '',
    } = $props();

    let active = $state(0);

    function handleSubmit(e) {
        e.preventDefault();
        const data = new FormData(e.currentTarget);
        onSubmit?.(String(data.get('email') ?? ''));
    }
</script>

<section aria-label="Hero" class={cn('relative w-full bg-background', className)}>
    <div class="mx-auto flex w-full max-w-7xl flex-col justify-center px-6 py-16 lg:py-20">
        <div class="flex flex-col-reverse justify-center gap-8 md:flex-row md:items-start md:gap-6 lg:gap-10 xl:gap-[72px]">
            <!-- left: text-tab rail + switchable preview -->
            <div class={cn('flex min-w-0 flex-col gap-5 md:w-[400px] md:shrink-0 md:flex-row md:gap-4 lg:w-[480px] lg:gap-6', badge && 'md:mt-11')}>
                <div role="tablist" aria-label="Preview switcher" class="flex shrink-0 gap-2 overflow-x-auto [scrollbar-width:none] md:flex-col md:overflow-visible [&::-webkit-scrollbar]:hidden">
                    {#each tabs as tab, i (tab.id)}
                        <button
                            type="button"
                            role="tab"
                            aria-selected={i === active}
                            onclick={() => (active = i)}
                            class={cn(
                                'whitespace-nowrap rounded-xl px-4 py-2.5 text-left text-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background',
                                i === active
                                    ? 'bg-muted font-semibold text-foreground shadow-sm ring-1 ring-border'
                                    : 'font-medium text-muted-foreground hover:bg-muted/50 hover:text-foreground',
                            )}
                        >
                            {tab.label}
                        </button>
                    {/each}
                </div>

                <div class="relative w-full min-w-0 md:flex-1">
                    {#each tabs as tab, i (tab.id)}
                        <div role="tabpanel" aria-hidden={i !== active} class={cn('transition-opacity duration-500', i === active ? 'relative opacity-100' : 'pointer-events-none absolute inset-0 opacity-0')}>
                            {@render tab.media?.()}
                        </div>
                    {/each}
                </div>
            </div>

            <!-- right: content -->
            <div class="flex min-w-0 flex-col items-center text-center md:max-w-[496px] md:flex-1 md:items-start md:text-left">
                {#if badge}
                    <div class="mb-4 flex w-fit items-center gap-2 rounded-lg bg-muted py-1 pl-1.5 pr-2.5">
                        {#if badge.tag}
                            <span class="inline-flex h-4 items-center rounded-[5px] bg-background px-1.5 text-[10px] font-semibold uppercase tracking-wide text-primary shadow-sm">{badge.tag}</span>
                        {/if}
                        <span class="text-sm text-muted-foreground">{badge.label}</span>
                    </div>
                {/if}

                <h1 class="mb-4 text-balance text-3xl font-semibold tracking-tight text-foreground sm:text-4xl lg:mb-5 lg:text-5xl xl:text-[56px] xl:leading-[1.05]">
                    {@render title()}
                </h1>

                {#if description}
                    <p class="text-balance text-base text-muted-foreground lg:text-lg">{@render description()}</p>
                {/if}

                {#if ratings.length > 0}
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-2 md:justify-start lg:mt-8">
                        {#each ratings as r, i (r.source + i)}
                            <div class="inline-flex items-center gap-1.5 rounded-full border border-border bg-muted/40 py-1 pl-2 pr-3">
                                <Star class="size-3.5 fill-amber-400 text-amber-400" />
                                <span class="text-sm font-semibold text-foreground">{r.score}</span>
                                <span class="text-sm text-muted-foreground">{r.source}</span>
                            </div>
                        {/each}
                    </div>
                {/if}

                {#if showEmail}
                    <form onsubmit={handleSubmit} class="mt-6 lg:mt-8">
                        <div class="mx-auto flex w-full max-w-[420px] flex-col gap-2 md:mx-0">
                            <label for="hero-email" class="text-sm text-muted-foreground">{emailLabel}</label>
                            <div class="flex items-center gap-2 rounded-xl border border-border bg-background px-3 shadow-sm transition focus-within:border-foreground focus-within:ring-2 focus-within:ring-ring">
                                <Mail class="size-5 shrink-0 text-muted-foreground" />
                                <input id="hero-email" name="email" type="email" placeholder={emailPlaceholder} class="h-10 w-full bg-transparent text-sm text-foreground outline-none placeholder:text-muted-foreground" />
                            </div>

                            {#if primaryCta || secondaryCta}
                                <div class="mt-2 flex flex-wrap justify-center gap-3 md:justify-start">
                                    {#if primaryCta}
                                        {#if primaryCta.href}
                                            <a href={primaryCta.href} class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-primary px-3.5 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                                                {primaryCta.label}
                                                <ArrowUpRight class="size-4 shrink-0" />
                                            </a>
                                        {:else}
                                            <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-primary px-3.5 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                                                {primaryCta.label}
                                                <ArrowUpRight class="size-4 shrink-0" />
                                            </button>
                                        {/if}
                                    {/if}
                                    {#if secondaryCta}
                                        <a href={secondaryCta.href ?? '#'} class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-muted px-3.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-background hover:text-foreground hover:shadow-sm hover:ring-1 hover:ring-border focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                                            {secondaryCta.label}
                                        </a>
                                    {/if}
                                </div>
                            {/if}
                        </div>
                    </form>
                {:else if primaryCta || secondaryCta}
                    <div class="mt-6 flex flex-wrap justify-center gap-3 md:justify-start lg:mt-8">
                        {#if primaryCta}
                            <a href={primaryCta.href ?? '#'} class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-primary px-3.5 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                                {primaryCta.label}
                                <ArrowUpRight class="size-4 shrink-0" />
                            </a>
                        {/if}
                        {#if secondaryCta}
                            <a href={secondaryCta.href ?? '#'} class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-muted px-3.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-background hover:text-foreground hover:shadow-sm hover:ring-1 hover:ring-border focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                                {secondaryCta.label}
                            </a>
                        {/if}
                    </div>
                {/if}

                {#if avatars.length > 0 || socialProof}
                    <div class="mt-6 flex flex-col items-center gap-y-3 md:flex-row">
                        {#if avatars.length > 0}
                            <div class="flex items-center">
                                {#each avatars as a, i (i)}
                                    <span class={cn('flex size-7 items-center justify-center overflow-hidden rounded-full border-2 border-background bg-muted text-[10px] font-medium text-muted-foreground', i > 0 && '-ml-2')}>
                                        {#if a.src}
                                            <img src={a.src} alt="" class="size-full object-cover" />
                                        {:else}
                                            {a.initials}
                                        {/if}
                                    </span>
                                {/each}
                            </div>
                        {/if}
                        {#if socialProof}
                            <span class="text-sm text-muted-foreground md:ml-3">{socialProof}</span>
                        {/if}
                    </div>
                {/if}
            </div>
        </div>
    </div>

    {#if logos.length > 0}
        <div class="border-y border-border">
            <div class="mx-auto max-w-7xl lg:px-7">
                <div class="flex items-center overflow-x-auto [scrollbar-width:none] lg:overflow-visible [&::-webkit-scrollbar]:hidden">
                    {#each logos as l, i (l.name)}
                        <div class="flex shrink-0 items-center lg:w-full lg:shrink">
                            <div class="flex w-full items-center justify-center px-6 py-5 lg:px-0 lg:py-7">
                                {@render l.logo?.()}
                            </div>
                            {#if i < logos.length - 1}
                                <div aria-hidden="true" class="h-9 w-px shrink-0 bg-border"></div>
                            {/if}
                        </div>
                    {/each}
                </div>
            </div>
        </div>
    {/if}
</section>
