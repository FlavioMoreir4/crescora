<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { index, show, edit, destroy } from '@/routes/units';
import { ArrowLeft, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

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

const props = defineProps<{
    unit: Unit;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Unidades', href: index.url() },
            { title: 'Unidade', href: '' },
        ],
    },
});

const deleteDialogOpen = ref(false);

function handleDelete(): void {
    deleteDialogOpen.value = true;
}

function confirmDelete(): void {
    router.delete(destroy.url(props.unit.slug));
}
</script>

<template>
    <Head :title="unit.name" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link :href="index.url()">
                    <Button variant="ghost" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ unit.name }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Slug: {{ unit.slug }} • Criada em
                        {{
                            new Date(unit.created_at).toLocaleDateString(
                                'pt-BR',
                            )
                        }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="edit.url(unit.slug)">
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
            :title="`Excluir unidade '${unit.name}'?`"
            description="Essa ação não pode ser desfeita."
            confirm-label="Excluir"
            @confirm="confirmDelete"
        />

        <Card>
            <CardHeader>
                <CardTitle>Informações</CardTitle>
                <CardDescription>Dados da unidade.</CardDescription>
            </CardHeader>
            <CardContent class="space-y-3">
                <div>
                    <span class="text-sm font-medium">Nome</span>
                    <p class="text-sm text-muted-foreground">{{ unit.name }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium">Slug</span>
                    <p class="text-sm text-muted-foreground">{{ unit.slug }}</p>
                </div>

                <div>
                    <span class="text-sm font-medium">Descrição</span>
                    <p class="text-sm text-muted-foreground">
                        {{ unit.description }}
                    </p>
                </div>
                <div>
                    <span class="text-sm font-medium">Telefone</span>
                    <p class="text-sm text-muted-foreground">
                        {{ unit.phone }}
                    </p>
                </div>
                <div>
                    <span class="text-sm font-medium">Email</span>
                    <p class="text-sm text-muted-foreground">
                        {{ unit.email }}
                    </p>
                </div>
                <div>
                    <span class="text-sm font-medium">Endereço</span>
                    <p class="text-sm text-muted-foreground">
                        {{ unit.address }}
                    </p>
                </div>
                <div>
                    <span class="text-sm font-medium">Cidade</span>
                    <p class="text-sm text-muted-foreground">{{ unit.city }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium">Estado</span>
                    <p class="text-sm text-muted-foreground">
                        {{ unit.state }}
                    </p>
                </div>
                <div>
                    <span class="text-sm font-medium">CEP</span>
                    <p class="text-sm text-muted-foreground">{{ unit.zip }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium">Ativo</span>
                    <p class="text-sm text-muted-foreground">
                        {{ unit.is_active ? 'Sim' : 'Não' }}
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
