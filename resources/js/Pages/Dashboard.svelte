<script>
    import { Link } from '@inertiajs/svelte';
    import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.svelte';
    import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '$lib/components/ui/card';
    import { Button } from '$lib/components/ui/button';
    import {
        Chart as ChartJS,
        CategoryScale,
        LinearScale,
        PointElement,
        LineElement,
        Title,
        Tooltip,
        Legend
    } from 'chart.js';
    import { Line } from 'svelte-chartjs';

    ChartJS.register(
        CategoryScale,
        LinearScale,
        PointElement,
        LineElement,
        Title,
        Tooltip,
        Legend
    );

    let { stats, recentReceipts, recentlyCompleted, currency } = $props();

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { enabled: true } },
        scales: {
            x: { display: false },
            y: { display: false, beginAtZero: true }
        },
        elements: {
            point: { radius: 0, hitRadius: 10, hoverRadius: 4 },
            line: { tension: 0.4, borderWidth: 2 }
        }
    };

    function getChartData(data, color) {
        return {
            labels: stats.trendLabels,
            datasets: [
                {
                    data: data,
                    borderColor: color,
                    borderWidth: 2,
                    tension: 0.4,
                }
            ]
        };
    }

    const statusStyles = {
        unpaid: 'bg-red-500/10 text-red-400',
        part_payment: 'bg-yellow-500/10 text-yellow-400',
        paid: 'bg-green-500/10 text-green-400',
    };

    const statusDot = {
        unpaid: 'bg-red-500',
        part_payment: 'bg-yellow-500',
        paid: 'bg-green-500',
    };

    function formatStatus(status) {
        return status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
    }

    function money(n) {
        return (Number(n) || 0).toFixed(2);
    }
</script>

<svelte:head>
    <title>Dashboard</title>
</svelte:head>

<AuthenticatedLayout>
    {#snippet header()}
        <h2 class="font-semibold text-xl text-white leading-tight">Dashboard</h2>
    {/snippet}

    <div class="space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader>
                        <CardDescription>Collected this month</CardDescription>
                        <CardTitle class="text-2xl">{money(stats.collectedThisMonth)} {currency}</CardTitle>
                    </CardHeader>
                    <CardContent class="h-16 pt-0 pb-4">
                        <Line data={getChartData(stats.collectedTrend, '#22c55e')} options={chartOptions} />
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardDescription>Unpaid Amount</CardDescription>
                        <CardTitle class="text-2xl text-red-500">{money(stats.unpaidAmount)} {currency}</CardTitle>
                    </CardHeader>
                    <CardContent class="h-16 pt-0 pb-4">
                        <!-- We can leave this empty or add a simple placeholder if no trend data for unpaid -->
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardDescription>Receipts this month</CardDescription>
                        <CardTitle class="text-2xl">{stats.receiptsThisMonth}</CardTitle>
                    </CardHeader>
                    <CardContent class="h-16 pt-0 pb-4">
                        <Line data={getChartData(stats.receiptsTrend, '#f97316')} options={chartOptions} />
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardDescription>Customers</CardDescription>
                        <CardTitle class="text-2xl">{stats.customerCount}</CardTitle>
                    </CardHeader>
                    <CardContent class="h-16 pt-0 pb-4">
                        <Line data={getChartData(stats.customersTrend, '#3b82f6')} options={chartOptions} />
                    </CardContent>
                </Card>
            </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <Card class="lg:col-span-2">
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle>Recent receipts</CardTitle>
                        <CardDescription>
                            {recentReceipts.length === 0 ? "Nothing here yet — create your first receipt to get started." : 'Your latest receipts.'}
                        </CardDescription>
                    </div>
                    <Link href="/invoices/create">
                        <Button>New receipt</Button>
                    </Link>
                </CardHeader>
                {#if recentReceipts.length > 0}
                    <CardContent class="p-0">
                        <table class="w-full text-sm text-left">
                            <tbody>
                                {#each recentReceipts as invoice (invoice.id)}
                                    <tr class="border-t border-white/10">
                                        <td class="px-6 py-3 font-medium text-white">
                                            <Link href={`/invoices/${invoice.id}/edit`} class="hover:underline">{invoice.number}</Link>
                                        </td>
                                        <td class="px-6 py-3 text-neutral-400">{invoice.customer?.name ?? '—'}</td>
                                        <td class="px-6 py-3">
                                            <span class={`inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium ${statusStyles[invoice.status]}`}>
                                                <span class={`h-1.5 w-1.5 rounded-full ${statusDot[invoice.status]}`}></span>
                                                {formatStatus(invoice.status)}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-right text-white">{invoice.total} {invoice.currency}</td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </CardContent>
                {/if}
            </Card>

            {#if recentlyCompleted.length > 0}
                <Card>
                    <CardHeader>
                        <CardTitle>Recently completed</CardTitle>
                        <CardDescription>Receipts that were fully paid off, including ones that started as part payments.</CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <table class="w-full text-sm text-left">
                            <tbody>
                                {#each recentlyCompleted as invoice (invoice.id)}
                                    <tr class="border-t border-white/10">
                                        <td class="px-6 py-3 font-medium text-white">
                                            <Link href={`/invoices/${invoice.id}/edit`} class="hover:underline">{invoice.number}</Link>
                                        </td>
                                        <td class="px-6 py-3 text-neutral-400">{invoice.customer?.name ?? '—'}</td>
                                        <td class="px-6 py-3 text-neutral-400">{invoice.completed_at ? invoice.completed_at.substring(0, 10) : ''}</td>
                                        <td class="px-6 py-3 text-right text-white">{invoice.total} {invoice.currency}</td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </CardContent>
                </Card>
            {/if}
        </div>
    </div>
</AuthenticatedLayout>
