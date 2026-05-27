<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { index, store, download } from '@/routes/exports';
import type { ColumnDef } from '@tanstack/vue-table';
import { Plus, Download } from 'lucide-vue-next';
import { h } from 'vue';
import DataTable from '@/components/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

interface ExportItem {
    id: number;
    type: string;
    status: string;
    file_name: string | null;
    file_size: number | null;
    error: string | null;
    completed_at: string | null;
    created_at: string;
    user: { name: string } | null;
}

defineProps<{
    exports: {
        data: ExportItem[];
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
        breadcrumbs: [{ title: 'Exportações', href: index.url() }],
    },
});

const typeColors: Record<string, string> = {
    leads: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
    forms: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
};

const typeLabels: Record<string, string> = {
    leads: 'Leads',
    forms: 'Formulários',
};

const statusColors: Record<string, string> = {
    pending:
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    completed:
        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    failed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
};

const statusLabels: Record<string, string> = {
    pending: 'Pendente',
    processing: 'Processando',
    completed: 'Concluído',
    failed: 'Falhou',
};

const columns: ColumnDef<ExportItem>[] = [
    {
        accessorKey: 'type',
        header: 'Tipo',
        cell: ({ row }) => {
            const type = row.original.type;

            return h(
                Badge,
                {
                    variant: 'outline' as any,
                    class: typeColors[type] || '',
                },
                () => typeLabels[type] || type,
            );
        },
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
        accessorKey: 'user.name',
        header: 'Solicitado por',
        cell: ({ row }) => row.original.user?.name || '-',
    },
    {
        accessorKey: 'created_at',
        header: 'Criado em',
        cell: ({ row }) =>
            new Date(row.original.created_at).toLocaleDateString('pt-BR'),
    },
    {
        id: 'download',
        header: 'Ações',
        cell: ({ row }) => {
            if (
                row.original.status !== 'completed' ||
                !row.original.file_name
            ) {
                return h(
                    'span',
                    { class: 'text-muted-foreground text-sm' },
                    '-',
                );
            }

            return h(
                'a',
                {
                    href: download.url(row.original.id),
                    class: 'inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline',
                },
                [h(Download, { class: 'h-4 w-4' }), ' Baixar'],
            );
        },
    },
];
</script>

<template>
    <Head title="Exportações" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Exportações</h1>
                <p class="text-sm text-muted-foreground">
                    Gerencie as exportações de dados.
                </p>
            </div>
            <Button disabled variant="outline">
                <Plus class="mr-2 h-4 w-4" />
                Nova Exportação
            </Button>
        </div>

        <DataTable
            :columns="columns"
            :data="exports.data"
            searchable
            search-placeholder="Pesquisar exportações..."
            empty-message="Nenhuma exportação encontrada."
        />
    </div>
</template>
