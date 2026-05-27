<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { CreditCard, Check, X, Loader2, ArrowRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

interface Plan {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price_monthly: number;
    price_yearly: number;
    features: string[] | null;
    max_leads: number | null;
    max_users: number | null;
    max_units: number | null;
}

interface Subscription {
    id: number;
    status: string;
    amount: string;
    billing_period: string;
    payment_method: string | null;
    current_period_ends_at: string | null;
    plan: Plan | null;
}

const props = defineProps<{
    subscription: Subscription | null;
    plans: Plan[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Assinatura', href: '/billing' }],
    },
});

const isYearly = ref(false);
const subscribing = ref(false);
const canceling = ref(false);

const statusColors: Record<string, string> = {
    active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    trialing: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    past_due:
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    canceled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    expired: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    pending: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
};

const statusLabels: Record<string, string> = {
    active: 'Ativo',
    trialing: 'Período de Teste',
    past_due: 'Pagamento Pendente',
    canceled: 'Cancelado',
    expired: 'Expirado',
    pending: 'Pendente',
};

const paymentLabels: Record<string, string> = {
    pix: 'PIX',
    credit_card: 'Cartão de Crédito',
    boleto: 'Boleto',
};

function subscribe(planId: number) {
    subscribing.value = true;
    router.post(
        '/billing/subscribe',
        {
            plan_id: planId,
            billing_period: isYearly.value ? 'yearly' : 'monthly',
            payment_method: 'pix',
        },
        {
            onFinish: () => {
                subscribing.value = false;
            },
        },
    );
}

function cancel() {
    if (!confirm('Tem certeza que deseja cancelar sua assinatura?')) return;
    canceling.value = true;
    router.post(
        '/billing/cancel',
        {},
        {
            onFinish: () => {
                canceling.value = false;
            },
        },
    );
}

function formatPrice(price: number): string {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(price);
}
</script>

<template>
    <Head title="Assinatura" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Assinatura</h1>
                <p class="text-sm text-muted-foreground">
                    Gerencie seu plano e forma de pagamento.
                </p>
            </div>
        </div>

        <template v-if="subscription && subscription.plan">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-3">
                        {{ subscription.plan.name }}
                        <Badge
                            :class="statusColors[subscription.status] || ''"
                            variant="outline"
                        >
                            {{
                                statusLabels[subscription.status] ||
                                subscription.status
                            }}
                        </Badge>
                    </CardTitle>
                    <CardDescription>
                        Plano
                        {{
                            subscription.billing_period === 'yearly'
                                ? 'anual'
                                : 'mensal'
                        }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div class="text-3xl font-bold">
                        {{ formatPrice(Number(subscription.amount)) }}
                        <span class="text-sm font-normal text-muted-foreground"
                            >/mês</span
                        >
                    </div>
                    <div class="text-sm text-muted-foreground">
                        Pagamento via
                        {{
                            paymentLabels[subscription.payment_method || ''] ||
                            subscription.payment_method
                        }}
                    </div>
                    <div
                        v-if="subscription.current_period_ends_at"
                        class="text-sm text-muted-foreground"
                    >
                        Próxima cobrança:
                        {{
                            new Date(
                                subscription.current_period_ends_at,
                            ).toLocaleDateString('pt-BR')
                        }}
                    </div>
                </CardContent>
                <CardFooter>
                    <Button
                        variant="destructive"
                        :disabled="canceling"
                        @click="cancel"
                    >
                        <Loader2
                            v-if="canceling"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        <X v-else class="mr-2 h-4 w-4" />
                        Cancelar Assinatura
                    </Button>
                </CardFooter>
            </Card>
        </template>

        <template v-else>
            <div class="flex justify-center">
                <Button variant="outline" @click="isYearly = !isYearly">
                    {{ isYearly ? 'Mensal' : 'Anual' }}
                    <span class="ml-2 text-xs text-muted-foreground">
                        {{ isYearly ? '▼' : '▲' }}
                    </span>
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="plan in plans"
                    :key="plan.id"
                    class="flex flex-col"
                >
                    <CardHeader>
                        <CardTitle>{{ plan.name }}</CardTitle>
                        <CardDescription>{{
                            plan.description
                        }}</CardDescription>
                        <div class="mt-4">
                            <span class="text-3xl font-bold">
                                {{
                                    formatPrice(
                                        isYearly
                                            ? plan.price_yearly
                                            : plan.price_monthly,
                                    )
                                }}
                            </span>
                            <span class="text-sm text-muted-foreground"
                                >/mês</span
                            >
                        </div>
                    </CardHeader>
                    <CardContent class="flex-1">
                        <ul v-if="plan.features" class="space-y-2">
                            <li
                                v-for="(feature, idx) in plan.features"
                                :key="idx"
                                class="flex items-center gap-2 text-sm"
                            >
                                <Check
                                    class="h-4 w-4 shrink-0 text-green-500"
                                />
                                {{ feature }}
                            </li>
                        </ul>
                        <ul class="mt-2 space-y-2">
                            <li
                                v-if="plan.max_leads"
                                class="flex items-center gap-2 text-sm text-muted-foreground"
                            >
                                Até {{ plan.max_leads }} leads
                            </li>
                            <li
                                v-if="plan.max_users"
                                class="flex items-center gap-2 text-sm text-muted-foreground"
                            >
                                Até {{ plan.max_users }} usuários
                            </li>
                            <li
                                v-if="plan.max_units"
                                class="flex items-center gap-2 text-sm text-muted-foreground"
                            >
                                Até {{ plan.max_units }} unidades
                            </li>
                        </ul>
                    </CardContent>
                    <CardFooter>
                        <Button
                            class="w-full"
                            :disabled="subscribing"
                            @click="subscribe(plan.id)"
                        >
                            <Loader2
                                v-if="subscribing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            <ArrowRight v-else class="mr-2 h-4 w-4" />
                            Assinar com PIX
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </template>
    </div>
</template>
