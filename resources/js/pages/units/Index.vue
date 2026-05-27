<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { index, create, show, edit, destroy } from '@/routes/units';
import { h, ref } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import DataTable from '@/components/DataTable.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';

interface Unit {
    id: number;
    name: string;
    slug: string;
    description: string;
    phone: string;
    email: string;
    address: string;
    city: string;
    state: string;
    zip: string;
    is_active: boolean;
    created_at: string;
}

defineProps<{
    units: {
        data: Unit[];
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
        breadcrumbs: [{ title: 'Unidades', href: index.url() }],
    },
});

const alertDialogOpen = ref(false);
const alertDialogMessage = ref('');
const unitToDelete = ref<Unit | null>(null);

const columns: ColumnDef<Unit>[] = [
    {
        accessorKey: 'name',
        header: 'Nome',
        cell: ({ row }) =>
            h(
                Link,
                {
                    href: show.url(row.original.slug),
                    class: 'font-medium hover:underline',
                },
                () => row.original.name,
            ),
    },
    { accessorKey: 'slug', header: 'Slug' },
    {
        accessorKey: 'city_state',
        header: 'Cidade / Estado',
        cell: ({ row }) => `${row.original.city}, ${row.original.state}`,
    },
    {
        accessorKey: 'is_active',
        header: 'Ativo',
        cell: ({ row }) => (row.original.is_active ? 'Sim' : 'Não'),
    },
    {
        accessorKey: 'created_at',
        header: 'Criado em',
        cell: ({ row }) =>
            new Date(row.original.created_at).toLocaleDateString('pt-BR'),
    },
];

function handleDelete(unit: Unit) {
    unitToDelete.value = unit;

    alertDialogOpen.value = true;
    alertDialogMessage.value = `Excluir unidade "${unit.name}"?`;
}

function handleConfirmDelete() {
    if (unitToDelete.value) {
        router.delete(destroy.url(unitToDelete.value.slug));
    }

    unitToDelete.value = null;
    alertDialogOpen.value = false;
}
</script>

<template>
    <Head title="Unidades" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Unidades</h1>
                <p class="text-sm text-muted-foreground">
                    Gerencie as unidades e franquias.
                </p>
            </div>
            <Link :href="create.url()">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    Nova Unidade
                </Button>
            </Link>
        </div>

        <AlertDialog :open="alertDialogOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{{
                        alertDialogMessage
                    }}</AlertDialogTitle>
                    <AlertDialogDescription>
                        Essa ação não pode ser desfeita.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="alertDialogOpen = false"
                        >Cancel</AlertDialogCancel
                    >
                    <AlertDialogAction @click="handleConfirmDelete(unit)"
                        >Continue</AlertDialogAction
                    >
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <DataTable
            :columns="columns"
            :data="units.data"
            searchable
            search-placeholder="Pesquisar por nome..."
            :actions="[
                {
                    label: 'Editar',
                    icon: Pencil,
                    handler: (u: Unit) => router.visit(edit.url(u.slug)),
                },
                {
                    label: 'Excluir',
                    icon: Trash2,
                    handler: handleDelete,
                    variant: 'destructive',
                },
            ]"
            empty-message="Nenhuma unidade cadastrada."
        />
    </div>
</template>
