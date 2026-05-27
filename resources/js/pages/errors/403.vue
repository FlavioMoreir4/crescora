<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ShieldX } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
</script>

<template>
    <Head title="Acesso Negado" />

    <div
        class="flex min-h-dvh items-center justify-center bg-gradient-to-b from-zinc-50 to-white p-4 dark:from-zinc-950 dark:to-zinc-900"
    >
        <div class="flex flex-col items-center gap-6 text-center">
            <div
                class="flex size-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30"
            >
                <ShieldX class="size-8 text-red-600 dark:text-red-400" />
            </div>

            <div class="space-y-2">
                <h1
                    class="text-6xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50"
                >
                    403
                </h1>
                <p class="text-lg text-zinc-600 dark:text-zinc-400">
                    Você não tem permissão para acessar esta página.
                </p>
            </div>

            <Button variant="default" as-child>
                <Link :href="route('dashboard')"> Voltar ao Início </Link>
            </Button>
        </div>
    </div>
</template>
