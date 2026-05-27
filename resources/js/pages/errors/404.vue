<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FileQuestion } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
</script>

<template>
    <Head title="Página Não Encontrada" />

    <div
        class="flex min-h-dvh items-center justify-center bg-gradient-to-b from-zinc-50 to-white p-4 dark:from-zinc-950 dark:to-zinc-900"
    >
        <div class="flex flex-col items-center gap-6 text-center">
            <div
                class="flex size-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30"
            >
                <FileQuestion
                    class="size-8 text-amber-600 dark:text-amber-400"
                />
            </div>

            <div class="space-y-2">
                <h1
                    class="text-6xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50"
                >
                    404
                </h1>
                <p class="text-lg text-zinc-600 dark:text-zinc-400">
                    A página que você procura não foi encontrada.
                </p>
            </div>

            <Button variant="default" as-child>
                <Link :href="route('dashboard')"> Voltar ao Início </Link>
            </Button>
        </div>
    </div>
</template>
