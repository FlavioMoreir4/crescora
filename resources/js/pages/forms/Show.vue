<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Trash2, Eye } from 'lucide-vue-next';
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
import { index, edit, destroy } from '@/routes/forms';
import { show as publicFormShow } from '@/routes/public/forms';

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
    metadata: Record<string, any> | null;
    created_at: string;
}

interface Form {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    config: {
        lead_assignment?: {
            mode?: string;
            owner_id?: number | null;
        };
    } | null;
    created_at: string;
    fields: FormField[];
    submissions: FormSubmission[];
    fields_count: number;
    submissions_count: number;
}

interface UnitLink {
    id: number;
    name: string;
    slug: string;
}

const props = defineProps<{
    form: Form;
    units: UnitLink[];
    teamMembers: { id: number; name: string }[];
}>();

const page = usePage();
const currentTeamSlug = computed(() => page.props.currentTeam?.slug ?? '');
const deleteDialogOpen = ref(false);

defineOptions({
    layout: (pageProps: { form?: { name: string } }) => ({
        breadcrumbs: [
            { title: 'Formulários', href: index.url() },
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

const fieldLabels = computed<Record<string, string>>(() =>
    Object.fromEntries(
        props.form.fields.map((field) => [field.name, field.label || field.name]),
    ),
);

const leadAssignmentModeLabel: Record<string, string> = {
    distribution: 'Distribuição automática',
    fixed: 'Responsável fixo',
    manual: 'Manual',
};

const leadAssignmentOwner = computed(() =>
    props.teamMembers.find(
        (member) =>
            member.id === props.form.config?.lead_assignment?.owner_id,
    ) ?? null,
);

function submissionLabel(key: string): string {
    return fieldLabels.value[key] ?? key.replaceAll('_', ' ');
}

function submissionValue(value: unknown): string {
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

function metadataEntries(metadata: Record<string, any> | null): Array<[string, any]> {
    return Object.entries(metadata ?? {});
}

function handleDelete(): void {
    deleteDialogOpen.value = true;
}

function confirmDelete(): void {
    router.delete(destroy.url(props.form.slug));
}
</script>

<template>
    <Head :title="form.name" />

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
                <Link :href="edit.url(form.slug)">
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
            :title="`Excluir formulário '${form.name}'?`"
            description="Essa ação não pode ser desfeita."
            confirm-label="Excluir"
            @confirm="confirmDelete"
        />

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
                <CardTitle>Atribuição de lead</CardTitle>
                <CardDescription>
                    Como os leads captados por este formulário são
                    encaminhados.
                </CardDescription>
            </CardHeader>
            <CardContent class="grid gap-4 md:grid-cols-2">
                <div>
                    <span class="text-sm font-medium">Estratégia</span>
                    <p class="text-sm text-muted-foreground">
                        {{
                            leadAssignmentModeLabel[
                                form.config?.lead_assignment?.mode || 'distribution'
                            ] || 'Distribuição automática'
                        }}
                    </p>
                </div>
                <div>
                    <span class="text-sm font-medium">Responsável padrão</span>
                    <p class="text-sm text-muted-foreground">
                        {{ leadAssignmentOwner?.name || '—' }}
                    </p>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Links Públicos</CardTitle>
                <CardDescription>
                    Compartilhe o formulário com a URL da unidade.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="units.length > 0" class="grid gap-3 md:grid-cols-2">
                    <div
                        v-for="unit in units"
                        :key="unit.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="font-medium">{{ unit.name }}</p>
                                <p class="truncate text-xs text-muted-foreground">
                                    {{
                                    publicFormShow.url({
                                            teamSlug: currentTeamSlug,
                                            unitSlug: unit.slug,
                                            formSlug: form.slug,
                                        })
                                    }}
                                </p>
                            </div>
                            <Link
                                :href="
                                    publicFormShow.url({
                                        teamSlug: currentTeamSlug,
                                        unitSlug: unit.slug,
                                        formSlug: form.slug,
                                    })
                                "
                                target="_blank"
                            >
                                <Button variant="outline" size="sm">
                                    <Eye class="mr-2 h-4 w-4" />
                                    Abrir
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>
                <p v-else class="py-6 text-sm text-muted-foreground">
                    Nenhuma unidade cadastrada para gerar um link público.
                </p>
            </CardContent>
        </Card>

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
                        <div
                            v-if="metadataEntries(submission.metadata).length > 0"
                            class="mb-4 flex flex-wrap gap-2"
                        >
                            <Badge
                                v-for="[key, value] in metadataEntries(
                                    submission.metadata,
                                )"
                                :key="key"
                                variant="outline"
                            >
                                {{ key }}: {{ submissionValue(value) }}
                            </Badge>
                        </div>
                        <div class="grid gap-2 md:grid-cols-2">
                            <div
                                v-for="(value, key) in submission.data"
                                :key="key"
                                class="text-sm"
                            >
                                <span class="font-medium text-muted-foreground"
                                    >{{ submissionLabel(key) }}:</span
                                >
                                <span class="ml-1">
                                    {{ submissionValue(value) }}
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
