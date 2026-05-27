<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Bell, CheckCheck, ChevronRight } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    index as notificationsIndex,
    markAsRead,
    markAllAsRead,
    unreadCount as unreadCountRoute,
} from '@/routes/notifications';

interface NotificationData {
    message: string;
    url?: string;
}

interface NotificationItem {
    id: string;
    data: NotificationData;
    read_at: string | null;
    created_at: string;
}

const unreadCount = ref(0);
const notifications = ref<NotificationItem[]>([]);
const loading = ref(false);
const open = ref(false);

async function loadUnreadCount(): Promise<void> {
    try {
        const response = await fetch(unreadCountRoute().url);

        if (response.ok) {
            const data = await response.json();

            unreadCount.value = data.count;
        }
    } catch {
        // Silently fail — the bell just won't show a count
    }
}

async function loadNotifications(): Promise<void> {
    loading.value = true;

    try {
        const response = await fetch(notificationsIndex().url, {
            headers: {
                'X-Inertia': 'true',
                Accept: 'application/json',
            },
        });

        if (response.ok) {
            const json = await response.json();

            notifications.value = json.props?.notifications?.data ?? [];
        }
    } catch {
        notifications.value = [];
    } finally {
        loading.value = false;
    }
}

function handleMarkAsRead(id: string): void {
    router.post(
        markAsRead(id).url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                loadUnreadCount();
                loadNotifications();
            },
        },
    );
}

function handleMarkAllAsRead(): void {
    router.post(
        markAllAsRead().url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                unreadCount.value = 0;
                loadNotifications();
            },
        },
    );
}

function handleNotificationClick(notification: NotificationItem): void {
    open.value = false;

    if (!notification.read_at) {
        router.post(
            markAsRead(notification.id).url,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    if (notification.data?.url) {
                        router.visit(notification.data.url);
                    }
                },
            },
        );
    } else if (notification.data?.url) {
        router.visit(notification.data.url);
    }
}

onMounted(() => {
    loadUnreadCount();
    loadNotifications();
});
</script>

<template>
    <DropdownMenu v-model:open="open">
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                class="relative"
                aria-label="Notificações"
            >
                <Bell class="h-5 w-5" />
                <span
                    v-if="unreadCount > 0"
                    class="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] font-medium text-destructive-foreground"
                >
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-80">
            <DropdownMenuLabel class="flex items-center justify-between">
                <span>Notificações</span>
                <Button
                    v-if="unreadCount > 0"
                    variant="ghost"
                    size="sm"
                    class="h-auto text-xs font-normal"
                    @click="handleMarkAllAsRead"
                >
                    <CheckCheck class="mr-1 h-3.5 w-3.5" />
                    Marcar todas como lidas
                </Button>
            </DropdownMenuLabel>

            <DropdownMenuSeparator />

            <div
                v-if="loading"
                class="py-6 text-center text-sm text-muted-foreground"
            >
                Carregando...
            </div>

            <div
                v-else-if="notifications.length === 0"
                class="py-6 text-center text-sm text-muted-foreground"
            >
                Nenhuma notificação.
            </div>

            <template v-else>
                <DropdownMenuItem
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="flex cursor-pointer flex-col items-start gap-1 py-3"
                    :class="{
                        'bg-muted/50': !notification.read_at,
                    }"
                    @click="handleNotificationClick(notification)"
                >
                    <div class="flex w-full items-start justify-between gap-2">
                        <span
                            class="text-sm"
                            :class="{
                                'font-semibold': !notification.read_at,
                            }"
                        >
                            {{ notification.data.message }}
                        </span>
                        <ChevronRight
                            v-if="notification.data?.url"
                            class="mt-0.5 h-3.5 w-3.5 shrink-0 text-muted-foreground"
                        />
                    </div>
                    <span class="text-xs text-muted-foreground">
                        {{ notification.created_at }}
                    </span>
                </DropdownMenuItem>
            </template>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
