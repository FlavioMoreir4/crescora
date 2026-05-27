<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { h } from 'vue';
import DataTable from '@/components/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

interface Lead {
    id: number;
    name: string;
    status: string;
    unit: { name: string } | null;
    owner: { name: string } | null;
    created_at: string;
}

defineProps<{
    leads: {
        data: Lead[];
        meta: {
            current_page: number;
            last_page: number;
            from: number;
            to: number;
            total: number;
        };
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Leads', href: '/leads' }],
    },
});

const statusColors: Record<string, string> = {
    new: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    contacted:
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    qualified:
        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    proposal:
        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    negotiation:
        'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    won: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300',
    lost: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    archived: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
};

const statusLabels: Record<string, string> = {
    new: 'Novo',
    contacted: 'Contactado',
    qualified: 'Qualificado',
    proposal: 'Proposta',
    negotiation: 'Negociação',
    won: 'Ganho',
    lost: 'Perdido',
    archived: 'Arquivado',
};

const columns: ColumnDef<Lead>[] = [
    {
        accessorKey: 'name',
        header: 'Nome',
        cell: ({ row }) =>
            h(
                Link,
                {
                    href: `/leads/${row.original.id}`,
                    class: 'font-medium hover:underline',
                },
                () => row.original.name,
            ),
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => {
            const status = row.original.status;

            return h(
                Badge,
                {
                    variant: 'outline' as any,
                    class: statusColors[status] || '',
                },
                () => statusLabels[status] || status,
            );
        },
    },
    {
        accessorKey: 'unit.name',
        header: 'Unidade',
        cell: ({ row }) => row.original.unit?.name || '-',
    },
    {
        accessorKey: 'owner.name',
        header: 'Responsável',
        cell: ({ row }) => row.original.owner?.name || '-',
    },
    {
        accessorKey: 'created_at',
        header: 'Criado em',
        cell: ({ row }) => {
            const date = new Date(row.original.created_at);

            return date.toLocaleDateString('pt-BR');
        },
    },
];

function handleDelete(lead: Lead) {
    if (confirm(`Excluir lead "${lead.name}"?`)) {
        router.delete(`/leads/${lead.id}`);
    }
}
</script>

<template>
    <Head title="Leads" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Leads</h1>
                <p class="text-sm text-muted-foreground">
                    Gerencie seus leads e oportunidades.
                </p>
            </div>
            <Link href="/leads/create">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    Novo Lead
                </Button>
            </Link>
        </div>

        <DataTable
            :columns="columns"
            :data="leads.data"
            searchable
            search-placeholder="Pesquisar por nome..."
            :actions="[
                {
                    label: 'Editar',
                    icon: Pencil,
                    handler: (lead: Lead) =>
                        router.visit(`/leads/${lead.id}/edit`),
                },
                {
                    label: 'Excluir',
                    icon: Trash2,
                    handler: handleDelete,
                    variant: 'destructive',
                },
            ]"
            empty-message="Nenhum lead encontrado. Crie seu primeiro lead!"
        />
    </div>
</template>
