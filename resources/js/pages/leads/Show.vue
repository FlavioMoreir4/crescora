<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { index, edit, destroy } from '@/routes/leads';

interface StatusOption {
    value: string;
    label: string;
    color: string;
}

interface User {
    id: number;
    name: string;
}

interface Unit {
    id: number;
    name: string;
}

interface StatusHistory {
    id: number;
    from_status: string | null;
    to_status: string;
    actor: User | null;
    created_at: string;
}

interface AssignmentHistory {
    id: number;
    fromOwner: User | null;
    toOwner: User | null;
    actor: User | null;
    source: string;
    created_at: string;
}

interface Lead {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    document: string | null;
    source: string | null;
    data: Record<string, unknown> | null;
    status: string;
    notes: string | null;
    created_at: string;
    created_at_formatted?: string | null;
    updated_at_formatted?: string | null;
    last_contacted_at?: string | null;
    unit: Unit | null;
    owner: User | null;
    statusHistories?: StatusHistory[];
    status_histories?: StatusHistory[];
    assignmentHistories?: AssignmentHistory[];
    assignment_histories?: AssignmentHistory[];
}

const props = defineProps<{
    lead: Lead;
    statuses: StatusOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Leads', href: index.url() },
            { title: 'Lead', href: '' },
        ],
    },
});

const deleteDialogOpen = ref(false);
const leadDataEntries = computed(() =>
    Object.entries(props.lead.data ?? {}).filter(([, value]) => {
        return value !== null && value !== undefined && value !== '';
    }),
);
const statusHistoryEntries = computed(
    () => props.lead.status_histories ?? props.lead.statusHistories ?? [],
);
const assignmentHistoryEntries = computed(
    () => props.lead.assignment_histories ?? props.lead.assignmentHistories ?? [],
);

function labelFromKey(key: string): string {
    return key.replaceAll('_', ' ');
}

function formatValue(value: unknown): string {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (Array.isArray(value)) {
        return value.map((item) => String(item)).join(', ');
    }

    if (typeof value === 'object') {
        return JSON.stringify(value);
    }

    return String(value);
}

function sourceLabel(source: string | null): string {
    if (!source) {
        return '—';
    }

    if (source.startsWith('form:')) {
        return `Formulário: ${source.replace('form:', '')}`;
    }

    return source;
}

function assignmentSourceLabel(source: string): string {
    if (source === 'distribution') {
        return 'Distribuição automática';
    }

    if (source === 'form') {
        return 'Formulário';
    }

    if (source === 'creation') {
        return 'Criação';
    }

    return source;
}

function formatDate(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString('pt-BR');
}

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

function handleDelete(): void {
    deleteDialogOpen.value = true;
}

function confirmDelete(): void {
    router.delete(destroy.url(props.lead.id));
}
</script>

<template>
    <Head :title="lead.name" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link :href="index.url()">
                    <Button variant="ghost" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ lead.name }}
                        </h1>
                        <Badge
                            :class="statusColors[lead.status] || ''"
                            variant="outline"
                        >
                            {{ statusLabels[lead.status] || lead.status }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Lead #{{ lead.id }} • Criado em
                        {{ lead.created_at_formatted || formatDate(lead.created_at) }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="edit.url(lead.id)">
                    <Button variant="outline">
                        <Pencil class="mr-2 h-4 w-4" />
                        Editar
                    </Button>
                </Link>
                <Button variant="destructive" @click="handleDelete">
                    <Trash2 class="mr-2 h-4 w-4" />
                    Excluir
                </Button>
            </div>
        </div>

        <ConfirmActionDialog
            v-model:open="deleteDialogOpen"
            :title="`Excluir lead '${lead.name}'?`"
            description="Essa ação não pode ser desfeita."
            confirm-label="Excluir"
            @confirm="confirmDelete"
        />

        <div class="grid gap-6 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Informações</CardTitle>
                    <CardDescription>Dados de contato do lead.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div>
                        <span class="text-sm font-medium">E-mail</span>
                        <p class="text-sm text-muted-foreground">
                            {{ lead.email || '—' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm font-medium">Telefone</span>
                        <p class="text-sm text-muted-foreground">
                            {{ lead.phone || '—' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm font-medium">Documento</span>
                        <p class="text-sm text-muted-foreground">
                            {{ lead.document || '—' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm font-medium">Unidade</span>
                        <p class="text-sm text-muted-foreground">
                            {{ lead.unit?.name || '—' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm font-medium">Responsável</span>
                        <p class="text-sm text-muted-foreground">
                            {{ lead.owner?.name || '—' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm font-medium">Origem</span>
                        <p class="text-sm text-muted-foreground">
                            {{ sourceLabel(lead.source) }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm font-medium">Último contato</span>
                        <p class="text-sm text-muted-foreground">
                            {{ formatDate(lead.last_contacted_at) }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Observações</CardTitle>
                    <CardDescription>Anotações sobre o lead.</CardDescription>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">
                        {{ lead.notes || 'Nenhuma observação registrada.' }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Dados captados</CardTitle>
                <CardDescription>
                    Valores recebidos pelo formulário ou preenchidos manualmente.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div
                    v-if="leadDataEntries.length > 0"
                    class="grid gap-3 md:grid-cols-2"
                >
                    <div
                        v-for="[key, value] in leadDataEntries"
                        :key="key"
                        class="rounded-lg border p-4"
                    >
                        <p class="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                            {{ labelFromKey(key) }}
                        </p>
                        <p class="mt-2 text-sm">
                            {{ formatValue(value) }}
                        </p>
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-muted-foreground">
                    Nenhum dado adicional foi captado para este lead.
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Histórico de responsáveis</CardTitle>
                <CardDescription>
                    Mudanças de ownership registradas para este lead.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="assignmentHistoryEntries.length > 0" class="space-y-3">
                    <div
                        v-for="history in assignmentHistoryEntries"
                        :key="history.id"
                        class="flex flex-wrap items-center gap-3 text-sm"
                    >
                        <Badge variant="outline">
                            {{ history.fromOwner?.name || '—' }} →
                            {{ history.toOwner?.name || 'Sem responsável' }}
                        </Badge>
                        <span class="text-muted-foreground">
                            {{ assignmentSourceLabel(history.source) }}
                        </span>
                        <span class="text-muted-foreground">
                            por {{ history.actor?.name || 'sistema' }}
                        </span>
                        <span class="text-muted-foreground">
                            {{
                                new Date(history.created_at).toLocaleString(
                                    'pt-BR',
                                )
                            }}
                        </span>
                    </div>
                </div>
                <p v-else class="text-sm text-muted-foreground">
                    Nenhuma transferência de responsável registrada.
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Histórico de Status</CardTitle>
                <CardDescription>Alterações de status do lead.</CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="statusHistoryEntries.length > 0" class="space-y-3">
                    <div
                        v-for="h in statusHistoryEntries"
                        :key="h.id"
                        class="flex items-center gap-3 text-sm"
                    >
                        <Badge
                            :class="statusColors[h.to_status] || ''"
                            variant="outline"
                        >
                            {{ statusLabels[h.to_status] || h.to_status }}
                        </Badge>
                        <span class="text-muted-foreground">
                            por {{ h.actor?.name || 'sistema' }}
                        </span>
                        <span class="text-muted-foreground">
                            {{
                                new Date(h.created_at).toLocaleDateString(
                                    'pt-BR',
                                )
                            }}
                        </span>
                    </div>
                </div>
                <p v-else class="text-sm text-muted-foreground">
                    Nenhuma alteração de status registrada.
                </p>
            </CardContent>
        </Card>
    </div>
</template>
