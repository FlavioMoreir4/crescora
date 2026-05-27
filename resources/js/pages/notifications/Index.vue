<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Bell,
    CheckCheck,
    UserPlus,
    ArrowRightLeft,
    Check,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

interface NotificationData {
    type: string;
    lead_id?: number;
    lead_name?: string;
    unit_name?: string;
    message: string;
    from_status?: string;
    to_status?: string;
}

interface NotificationItem {
    id: string;
    type: string;
    data: NotificationData;
    read_at: string | null;
    created_at: string;
}

const props = defineProps<{
    notifications: {
        data: NotificationItem[];
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
        breadcrumbs: [{ title: 'Notificações', href: '/notifications' }],
    },
});

function relativeTime(dateString: string): string {
    const now = new Date();
    const date = new Date(dateString);
    const diffMs = now.getTime() - date.getTime();
    const diffSeconds = Math.floor(diffMs / 1000);

    if (diffSeconds < 60) return 'agora mesmo';

    const diffMinutes = Math.floor(diffSeconds / 60);
    if (diffMinutes < 60) {
        return `há ${diffMinutes} ${diffMinutes === 1 ? 'minuto' : 'minutos'}`;
    }

    const diffHours = Math.floor(diffMinutes / 60);
    if (diffHours < 24) {
        return `há ${diffHours} ${diffHours === 1 ? 'hora' : 'horas'}`;
    }

    const diffDays = Math.floor(diffHours / 24);
    if (diffDays < 7) {
        return `há ${diffDays} ${diffDays === 1 ? 'dia' : 'dias'}`;
    }

    if (diffDays < 30) {
        const weeks = Math.floor(diffDays / 7);

        return `há ${weeks} ${weeks === 1 ? 'semana' : 'semanas'}`;
    }

    return date.toLocaleDateString('pt-BR');
}

const iconMap: Record<string, typeof Bell> = {
    lead_assigned: UserPlus,
    lead_status_changed: ArrowRightLeft,
};

function getIcon(type: string): typeof Bell {
    return iconMap[type] || Bell;
}

function handleClick(notification: NotificationItem): void {
    if (!notification.read_at) {
        router.post(
            `/notifications/${notification.id}/read`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    if (notification.data.lead_id) {
                        router.visit(`/leads/${notification.data.lead_id}`);
                    }
                },
            },
        );
    } else if (notification.data.lead_id) {
        router.visit(`/leads/${notification.data.lead_id}`);
    }
}

function markAsRead(notification: NotificationItem): void {
    if (!notification.read_at) {
        router.post(
            `/notifications/${notification.id}/read`,
            {},
            {
                preserveScroll: true,
            },
        );
    }
}

function markAllAsRead(): void {
    router.post(
        '/notifications/mark-all-read',
        {},
        {
            preserveScroll: true,
        },
    );
}

function goToPage(page: number): void {
    router.visit(`/notifications?page=${page}`, {
        preserveScroll: true,
    });
}

const hasUnread = computed(() =>
    props.notifications.data.some((n) => !n.read_at),
);

const pages = computed(() => {
    const { current_page, last_page } = props.notifications.meta;
    const delta = 2;
    const range: (number | string)[] = [];

    for (let i = 1; i <= last_page; i++) {
        if (
            i === 1 ||
            i === last_page ||
            (i >= current_page - delta && i <= current_page + delta)
        ) {
            range.push(i);
        } else if (range[range.length - 1] !== '...') {
            range.push('...');
        }
    }

    return range;
});
</script>

<template>
    <Head title="Notificações" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Notificações</h1>
                <p class="text-sm text-muted-foreground">
                    Gerencie suas notificações
                </p>
            </div>
            <Button v-if="hasUnread" variant="outline" @click="markAllAsRead">
                <CheckCheck class="mr-2 h-4 w-4" />
                Marcar todas como lidas
            </Button>
        </div>

        <!-- Empty State -->
        <div
            v-if="!notifications.data.length"
            class="flex flex-col items-center justify-center gap-4 py-16"
        >
            <div
                class="flex h-16 w-16 items-center justify-center rounded-full bg-muted"
            >
                <Bell class="h-8 w-8 text-muted-foreground" />
            </div>
            <p class="text-sm text-muted-foreground">
                Nenhuma notificação encontrada.
            </p>
        </div>

        <!-- Notification List -->
        <div v-else class="flex flex-col gap-3">
            <div
                v-for="notification in notifications.data"
                :key="notification.id"
                class="flex items-start gap-4 rounded-xl border bg-card p-4 text-card-foreground shadow-sm transition-all duration-200 hover:bg-accent/50"
                :class="[
                    'cursor-pointer',
                    !notification.read_at
                        ? 'border-l-[3px] border-l-primary bg-muted/40'
                        : '',
                ]"
                @click="handleClick(notification)"
            >
                <!-- Icon -->
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                    :class="[
                        !notification.read_at
                            ? 'bg-primary/15'
                            : 'bg-muted-foreground/10',
                    ]"
                >
                    <component
                        :is="getIcon(notification.data.type)"
                        class="h-5 w-5"
                        :class="[
                            !notification.read_at
                                ? 'text-primary'
                                : 'text-muted-foreground',
                        ]"
                    />
                </div>

                <!-- Content -->
                <div class="min-w-0 flex-1 space-y-1.5">
                    <p
                        class="text-sm leading-relaxed"
                        :class="{
                            'font-semibold': !notification.read_at,
                        }"
                    >
                        {{ notification.data.message }}
                    </p>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-muted-foreground">
                            {{ relativeTime(notification.created_at) }}
                        </span>
                        <Badge
                            v-if="!notification.read_at"
                            variant="outline"
                            class="h-5 border-blue-200 bg-blue-50 px-1.5 py-0 text-[10px] leading-none text-blue-600 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-400"
                        >
                            Nova
                        </Badge>
                    </div>

                    <!-- Lead context -->
                    <p
                        v-if="
                            notification.data.lead_id &&
                            notification.data.lead_name
                        "
                        class="text-xs text-muted-foreground/70"
                    >
                        {{ notification.data.lead_name }}
                        <template v-if="notification.data.unit_name">
                            · {{ notification.data.unit_name }}
                        </template>
                    </p>
                </div>

                <!-- Mark as read button -->
                <Button
                    v-if="!notification.read_at"
                    variant="ghost"
                    size="icon"
                    class="mt-0.5 h-8 w-8 shrink-0"
                    @click.stop="markAsRead(notification)"
                >
                    <Check class="h-4 w-4" />
                    <span class="sr-only">Marcar como lida</span>
                </Button>
            </div>
        </div>

        <!-- Pagination -->
        <div
            v-if="notifications.meta.last_page > 1"
            class="flex items-center justify-center gap-1"
        >
            <Button
                variant="outline"
                size="sm"
                :disabled="notifications.meta.current_page <= 1"
                @click="goToPage(notifications.meta.current_page - 1)"
            >
                <ChevronLeft class="h-4 w-4" />
                <span class="sr-only">Página anterior</span>
            </Button>

            <template v-for="(page, idx) in pages" :key="idx">
                <span
                    v-if="page === '...'"
                    class="flex h-9 w-9 items-center justify-center text-sm text-muted-foreground"
                >
                    ...
                </span>
                <Button
                    v-else
                    variant="outline"
                    size="sm"
                    class="min-w-9"
                    :class="{
                        'border-primary bg-primary text-primary-foreground hover:bg-primary/90':
                            page === notifications.meta.current_page,
                    }"
                    @click="goToPage(page as number)"
                >
                    {{ page }}
                </Button>
            </template>

            <Button
                variant="outline"
                size="sm"
                :disabled="
                    notifications.meta.current_page >=
                    notifications.meta.last_page
                "
                @click="goToPage(notifications.meta.current_page + 1)"
            >
                <ChevronRight class="h-4 w-4" />
                <span class="sr-only">Próxima página</span>
            </Button>
        </div>
    </div>
</template>
