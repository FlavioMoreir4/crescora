<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

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

interface Lead {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    status: string;
    notes: string | null;
    created_at: string;
    unit: Unit | null;
    owner: User | null;
    statusHistories: StatusHistory[];
}

defineProps<{
    lead: Lead;
    statuses: StatusOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Leads', href: '/leads' },
            { title: 'Lead', href: '' },
        ],
    },
});

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

function handleDelete() {
    if (confirm(`Excluir lead "${lead.name}"?`)) {
        router.delete(`/leads/${lead.id}`);
    }
}
</script>

<template>
    <Head :title="lead.name" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link href="/leads">
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
                        {{
                            new Date(lead.created_at).toLocaleDateString(
                                'pt-BR',
                            )
                        }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="`/leads/${lead.id}/edit`">
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
                <CardTitle>Histórico de Status</CardTitle>
                <CardDescription>Alterações de status do lead.</CardDescription>
            </CardHeader>
            <CardContent>
                <div
                    v-if="
                        lead.statusHistories && lead.statusHistories.length > 0
                    "
                    class="space-y-3"
                >
                    <div
                        v-for="h in lead.statusHistories"
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
