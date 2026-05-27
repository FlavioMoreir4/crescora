<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowUpRight,
    Building2,
    ChevronRight,
    LayoutGrid,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { index as billingIndex } from '@/routes/billing';
import { index as leadsIndex } from '@/routes/leads';
import { index as unitsIndex } from '@/routes/units';
import type { Team } from '@/types';

interface LeadStat {
    status: string;
    label: string;
    color: string;
    count: number;
}

interface RecentLead {
    id: number;
    name: string;
    status: string;
    unit_name: string | null;
    created_at: string;
}

const props = defineProps<{
    leadStats: LeadStat[];
    totalLeads: number;
    unitCount: number;
    conversionRate: number;
    recentLeads: RecentLead[];
}>();

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: props.currentTeam
                    ? dashboard(props.currentTeam.slug)
                    : '/',
            },
        ],
    }),
});

const statusColorMap: Record<string, string> = {
    gray: 'bg-gray-500',
    blue: 'bg-blue-500',
    indigo: 'bg-indigo-500',
    violet: 'bg-violet-500',
    amber: 'bg-amber-500',
    green: 'bg-green-500',
    red: 'bg-red-500',
    neutral: 'bg-neutral-500',
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
            <p class="text-sm text-muted-foreground">
                Visão geral do seu negócio.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium"
                        >Total de Leads</CardTitle
                    >
                    <Users class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ totalLeads }}</div>
                    <p class="text-xs text-muted-foreground">
                        Leads cadastrados no sistema
                    </p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium">Unidades</CardTitle>
                    <Building2 class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ unitCount }}</div>
                    <p class="text-xs text-muted-foreground">
                        Unidades e franquias ativas
                    </p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium"
                        >Taxa de Conversão</CardTitle
                    >
                    <TrendingUp class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ conversionRate }}%</div>
                    <p class="text-xs text-muted-foreground">
                        Leads convertidos em vendas
                    </p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium"
                        >Leads por Status</CardTitle
                    >
                    <LayoutGrid class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent class="space-y-2">
                    <div
                        v-for="stat in leadStats"
                        :key="stat.status"
                        class="flex items-center justify-between gap-2"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-block h-2.5 w-2.5 rounded-full"
                                :class="
                                    statusColorMap[stat.color] ??
                                    'bg-muted-foreground'
                                "
                            />
                            <span class="text-xs text-muted-foreground">{{
                                stat.label
                            }}</span>
                        </div>
                        <span class="text-sm font-medium">{{
                            stat.count
                        }}</span>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <Link :href="leadsIndex().url" class="group">
                <Card
                    class="cursor-pointer transition-colors hover:bg-muted/50"
                >
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle class="text-sm font-medium">Leads</CardTitle>
                        <ArrowUpRight
                            class="h-4 w-4 text-muted-foreground transition-colors group-hover:text-foreground"
                        />
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">
                            Acesse o pipeline de leads, gerencie oportunidades e
                            acompanhe o funil de vendas.
                        </p>
                    </CardContent>
                </Card>
            </Link>
            <Link :href="unitsIndex().url" class="group">
                <Card
                    class="cursor-pointer transition-colors hover:bg-muted/50"
                >
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Unidades</CardTitle
                        >
                        <ArrowUpRight
                            class="h-4 w-4 text-muted-foreground transition-colors group-hover:text-foreground"
                        />
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">
                            Gerencie as unidades, franquias e suas
                            configurações.
                        </p>
                    </CardContent>
                </Card>
            </Link>
            <Link :href="billingIndex().url" class="group">
                <Card
                    class="cursor-pointer transition-colors hover:bg-muted/50"
                >
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Assinatura</CardTitle
                        >
                        <ArrowUpRight
                            class="h-4 w-4 text-muted-foreground transition-colors group-hover:text-foreground"
                        />
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">
                            Gerencie seu plano, forma de pagamento e histórico
                            de cobranças.
                        </p>
                    </CardContent>
                </Card>
            </Link>
        </div>

        <div class="flex-1 rounded-xl border border-sidebar-border/70">
            <div class="p-6">
                <h2 class="mb-4 text-lg font-semibold">Leads Recentes</h2>

                <div
                    v-if="recentLeads.length === 0"
                    class="py-8 text-center text-sm text-muted-foreground"
                >
                    Nenhum lead registrado recentemente.
                </div>

                <div v-else class="space-y-1">
                    <div
                        v-for="lead in recentLeads"
                        :key="lead.id"
                        class="flex items-center justify-between rounded-lg px-3 py-2.5 transition-colors hover:bg-muted/50"
                    >
                        <div class="flex flex-col gap-0.5">
                            <span class="text-sm font-medium">{{
                                lead.name
                            }}</span>
                            <span class="text-xs text-muted-foreground">
                                {{ lead.unit_name ?? '—' }}
                                &middot;
                                {{ lead.created_at }}
                            </span>
                        </div>
                        <Badge variant="secondary" class="shrink-0 capitalize">
                            {{ lead.status }}
                        </Badge>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
