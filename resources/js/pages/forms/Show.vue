<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Trash2, Eye } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

interface FormField {
    id: number;
    type: string;
    name: string;
    label: string;
    placeholder: string | null;
    options: string[] | null;
    is_required: boolean;
    order: number;
}

interface FormSubmission {
    id: number;
    data: Record<string, any>;
    created_at: string;
}

interface Form {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    created_at: string;
    fields: FormField[];
    submissions: FormSubmission[];
    fields_count: number;
    submissions_count: number;
}

const props = defineProps<{
    form: Form;
}>();

defineOptions({
    layout: (pageProps: { form?: { name: string } }) => ({
        breadcrumbs: [
            { title: 'Formulários', href: '/forms' },
            { title: pageProps.form?.name ?? '', href: '' },
        ],
    }),
});

const fieldTypeLabels: Record<string, string> = {
    text: 'Texto curto',
    textarea: 'Texto longo',
    email: 'E-mail',
    tel: 'Telefone',
    number: 'Número',
    select: 'Seleção',
    radio: 'Radio',
    checkbox: 'Caixa de seleção',
    cpf: 'CPF',
    cnpj: 'CNPJ',
    cep: 'CEP',
    date: 'Data',
};

function handleDelete() {
    if (confirm(`Excluir formulário "${form.name}"?`)) {
        router.delete(`/forms/${form.slug}`);
    }
}
</script>

<template>
    <Head :title="form.name" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link href="/forms">
                    <Button variant="ghost" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ form.name }}
                        </h1>
                        <Badge
                            :class="
                                form.is_active
                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300'
                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                            "
                            variant="outline"
                        >
                            {{ form.is_active ? 'Ativo' : 'Inativo' }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ form.fields_count }} campos •
                        {{ form.submissions_count }} respostas • Criado em
                        {{
                            new Date(form.created_at).toLocaleDateString(
                                'pt-BR',
                            )
                        }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="`/forms/${form.slug}/preview`">
                    <Button variant="outline">
                        <Eye class="mr-2 h-4 w-4" />
                        Visualizar
                    </Button>
                </Link>
                <Link :href="`/forms/${form.slug}/edit`">
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

        <div class="grid gap-6 lg:grid-cols-3">
            <Card class="lg:col-span-2">
                <CardHeader>
                    <CardTitle>Detalhes</CardTitle>
                    <CardDescription
                        >Informações do formulário.</CardDescription
                    >
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <span class="text-sm font-medium">Descrição</span>
                        <p class="text-sm text-muted-foreground">
                            {{ form.description || 'Nenhuma descrição.' }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Estatísticas</CardTitle>
                    <CardDescription
                        >Dados de uso do formulário.</CardDescription
                    >
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted-foreground"
                            >Campos</span
                        >
                        <span class="font-semibold">{{
                            form.fields_count
                        }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted-foreground"
                            >Submissões</span
                        >
                        <span class="font-semibold">{{
                            form.submissions_count
                        }}</span>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Campos</CardTitle>
                <CardDescription
                    >Ordem e configuração dos campos.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <div
                    v-if="form.fields && form.fields.length > 0"
                    class="space-y-3"
                >
                    <div
                        v-for="field in form.fields"
                        :key="field.id"
                        class="flex items-center justify-between rounded-lg border p-4"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded bg-muted text-muted-foreground"
                            >
                                {{ field.order }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{
                                        field.label || field.name
                                    }}</span>
                                    <Badge
                                        v-if="field.is_required"
                                        variant="outline"
                                        class="text-xs"
                                    >
                                        Obrigatório
                                    </Badge>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    <code class="text-xs">{{
                                        field.name
                                    }}</code>
                                    •
                                    {{
                                        fieldTypeLabels[field.type] ||
                                        field.type
                                    }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="field.options && field.options.length > 0"
                            class="text-right text-sm text-muted-foreground"
                        >
                            {{ field.options.length }} opções
                        </div>
                    </div>
                </div>
                <p
                    v-else
                    class="py-8 text-center text-sm text-muted-foreground"
                >
                    Nenhum campo configurado.
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Submissões Recentes</CardTitle>
                <CardDescription>Últimas respostas recebidas.</CardDescription>
            </CardHeader>
            <CardContent>
                <div
                    v-if="form.submissions && form.submissions.length > 0"
                    class="space-y-4"
                >
                    <div
                        v-for="submission in form.submissions"
                        :key="submission.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">
                                Submissão #{{ submission.id }}
                            </span>
                            <span class="text-sm text-muted-foreground">
                                {{
                                    new Date(
                                        submission.created_at,
                                    ).toLocaleString('pt-BR')
                                }}
                            </span>
                        </div>
                        <div class="grid gap-2 md:grid-cols-2">
                            <div
                                v-for="(value, key) in submission.data"
                                :key="key"
                                class="text-sm"
                            >
                                <span class="font-medium text-muted-foreground"
                                    >{{ key }}:</span
                                >
                                <span class="ml-1">
                                    {{
                                        Array.isArray(value)
                                            ? value.join(', ')
                                            : value || '—'
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <p
                    v-else
                    class="py-8 text-center text-sm text-muted-foreground"
                >
                    Nenhuma submissão ainda.
                </p>
            </CardContent>
        </Card>
    </div>
</template>
