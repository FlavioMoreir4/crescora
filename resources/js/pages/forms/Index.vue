<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { h } from 'vue';
import DataTable from '@/components/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

interface Form {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    fields_count: number;
    submissions_count: number;
    created_at: string;
}

defineProps<{
    forms: {
        data: Form[];
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
        breadcrumbs: [{ title: 'Formulários', href: '/forms' }],
    },
});

const columns: ColumnDef<Form>[] = [
    {
        accessorKey: 'name',
        header: 'Nome',
        cell: ({ row }) =>
            h(
                Link,
                {
                    href: `/forms/${row.original.slug}`,
                    class: 'font-medium hover:underline',
                },
                () => row.original.name,
            ),
    },
    {
        accessorKey: 'fields_count',
        header: 'Campos',
        cell: ({ row }) =>
            h(
                Badge,
                {
                    variant: 'outline' as any,
                    class: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                },
                () => `${row.original.fields_count} campos`,
            ),
    },
    {
        accessorKey: 'submissions_count',
        header: 'Submissões',
        cell: ({ row }) =>
            h(
                Badge,
                {
                    variant: 'outline' as any,
                    class: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                },
                () => `${row.original.submissions_count} respostas`,
            ),
    },
    {
        accessorKey: 'is_active',
        header: 'Status',
        cell: ({ row }) => {
            const isActive = row.original.is_active;

            return h(
                Badge,
                {
                    variant: 'outline' as any,
                    class: isActive
                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300'
                        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                },
                () => (isActive ? 'Ativo' : 'Inativo'),
            );
        },
    },
    {
        accessorKey: 'created_at',
        header: 'Criado em',
        cell: ({ row }) =>
            new Date(row.original.created_at).toLocaleDateString('pt-BR'),
    },
];

function handleDelete(form: Form) {
    if (confirm(`Excluir formulário "${form.name}"?`)) {
        router.delete(`/forms/${form.slug}`);
    }
}
</script>

<template>
    <Head title="Formulários" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Formulários</h1>
                <p class="text-sm text-muted-foreground">
                    Gerencie os formulários de contato e coleta de dados.
                </p>
            </div>
            <Link href="/forms/create">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    Novo Formulário
                </Button>
            </Link>
        </div>

        <DataTable
            :columns="columns"
            :data="forms.data"
            searchable
            search-placeholder="Pesquisar por nome..."
            :actions="[
                {
                    label: 'Editar',
                    icon: Pencil,
                    handler: (form: Form) =>
                        router.visit(`/forms/${form.slug}/edit`),
                },
                {
                    label: 'Excluir',
                    icon: Trash2,
                    handler: handleDelete,
                    variant: 'destructive',
                },
            ]"
            empty-message="Nenhum formulário encontrado. Crie seu primeiro formulário!"
        />
    </div>
</template>
