<script setup lang="ts" generic="TData">
import type { ColumnDef, PaginationState } from '@tanstack/vue-table';
import {
    useVueTable,
    getCoreRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    getFilteredRowModel,
    FlexRender,
} from '@tanstack/vue-table';
import {
    Search,
    ChevronLeft,
    ChevronRight,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    MoreHorizontal,
    Loader2,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { cn } from '@/lib/utils';

interface RowAction {
    label: string;
    icon?: any;
    handler: (row: TData) => void;
    variant?: 'default' | 'destructive';
}

interface DataTableProps {
    columns: ColumnDef<TData>[];
    data: TData[];
    loading?: boolean;
    searchable?: boolean;
    searchPlaceholder?: string;
    pageSize?: number;
    actions?: RowAction[];
    emptyMessage?: string;
}

const props = withDefaults(defineProps<DataTableProps>(), {
    loading: false,
    searchable: true,
    searchPlaceholder: 'Pesquisar...',
    pageSize: 10,
    actions: () => [],
    emptyMessage: 'Nenhum registro encontrado.',
});

const globalFilter = ref('');

const pagination = ref<PaginationState>({
    pageIndex: 0,
    pageSize: props.pageSize,
});

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onPaginationChange: (updater) => {
        if (typeof updater === 'function') {
            pagination.value = updater(pagination.value);
        } else {
            pagination.value = updater;
        }
    },
    state: {
        get pagination() {
            return pagination.value;
        },
        get globalFilter() {
            return globalFilter.value;
        },
    },
    globalFilterFn: 'includesString',
});

const pageStart = computed(() =>
    table.getRowModel().rows.length > 0
        ? pagination.value.pageIndex * pagination.value.pageSize + 1
        : 0,
);
const pageEnd = computed(() =>
    Math.min(
        (pagination.value.pageIndex + 1) * pagination.value.pageSize,
        table.getFilteredRowModel().rows.length,
    ),
);
const totalRows = computed(() => table.getFilteredRowModel().rows.length);
</script>

<template>
    <div class="space-y-4">
        <div v-if="searchable" class="flex items-center gap-2">
            <div class="relative max-w-sm flex-1">
                <Search
                    class="pointer-events-none absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                />
                <Input
                    :model-value="globalFilter"
                    @update:model-value="
                        globalFilter = $event;
                        pagination.pageIndex = 0;
                    "
                    :placeholder="searchPlaceholder"
                    class="pl-8"
                />
            </div>
            <slot name="actions" />
        </div>

        <div class="rounded-md border">
            <Table v-if="!loading && totalRows > 0">
                <TableHeader>
                    <TableRow
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                            :style="{
                                width:
                                    header.getSize() !== 150
                                        ? `${header.getSize()}px`
                                        : undefined,
                            }"
                            :class="{
                                'cursor-pointer select-none':
                                    header.column.getCanSort(),
                            }"
                            @click="
                                header.column.getCanSort() &&
                                header.column.toggleSorting()
                            "
                        >
                            <div class="flex items-center gap-1">
                                <FlexRender
                                    :render="header.column.columnDef.header"
                                />
                                <ArrowUpDown
                                    v-if="
                                        header.column.getCanSort() &&
                                        !header.column.getIsSorted()
                                    "
                                    class="h-3 w-3 text-muted-foreground/50"
                                />
                                <ArrowUp
                                    v-else-if="
                                        header.column.getIsSorted() === 'asc'
                                    "
                                    class="h-3 w-3"
                                />
                                <ArrowDown
                                    v-else-if="
                                        header.column.getIsSorted() === 'desc'
                                    "
                                    class="h-3 w-3"
                                />
                            </div>
                        </TableHead>
                        <TableHead v-if="actions.length > 0" class="w-[70px]">
                            <span class="sr-only">Ações</span>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="row in table.getRowModel().rows"
                        :key="row.id"
                        data-state="selected"
                    >
                        <TableCell
                            v-for="cell in row.getVisibleCells()"
                            :key="cell.id"
                        >
                            <FlexRender
                                :render="cell.column.columnDef.cell"
                                :props="cell.getContext()"
                            />
                        </TableCell>
                        <TableCell v-if="actions.length > 0">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" class="h-8 w-8 p-0">
                                        <span class="sr-only">Abrir menu</span>
                                        <MoreHorizontal class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuLabel>Ações</DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        v-for="(action, idx) in actions"
                                        :key="idx"
                                        @click="action.handler(row.original)"
                                        :class="{
                                            'text-destructive':
                                                action.variant ===
                                                'destructive',
                                        }"
                                    >
                                        <component
                                            :is="action.icon"
                                            v-if="action.icon"
                                            class="mr-2 h-4 w-4"
                                        />
                                        {{ action.label }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <div v-else-if="loading" class="space-y-3 p-4">
                <Skeleton v-for="i in 5" :key="i" class="h-10 w-full" />
            </div>

            <div v-else class="py-8 text-center text-sm text-muted-foreground">
                {{ emptyMessage }}
            </div>
        </div>

        <div
            class="flex items-center justify-between text-sm text-muted-foreground"
        >
            <div>
                Mostrando <span class="font-medium">{{ pageStart }}</span> a
                <span class="font-medium">{{ pageEnd }}</span> de
                <span class="font-medium">{{ totalRows }}</span> resultados
            </div>
            <div class="flex items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="!table.getCanPreviousPage()"
                    @click="table.previousPage()"
                >
                    <ChevronLeft class="h-4 w-4" />
                    Anterior
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="!table.getCanNextPage()"
                    @click="table.nextPage()"
                >
                    Próximo
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
