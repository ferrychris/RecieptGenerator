<script>
    import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.svelte';
    import { Card, CardHeader, CardTitle, CardDescription } from '$lib/components/ui/card';
</script>

<svelte:head>
    <title>Reports</title>
</svelte:head>

<AuthenticatedLayout title="Reports">
    <Card>
        <CardHeader>
            <CardTitle>Reports</CardTitle>
            <CardDescription>Reporting is coming soon.</CardDescription>
        </CardHeader>
    </Card>
</AuthenticatedLayout>
