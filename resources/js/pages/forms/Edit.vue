<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Trash2, GripVertical } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';

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

interface Form {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    fields: FormField[];
}

interface FieldRow {
    _id: number;
    id?: number;
    type: string;
    name: string;
    label: string;
    placeholder: string;
    is_required: boolean;
    options: string;
}

const props = defineProps<{
    form: Form;
    fieldTypes: string[];
}>();

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

defineOptions({
    layout: (pageProps: { form?: { name: string; slug: string } }) => ({
        breadcrumbs: [
            { title: 'Formulários', href: '/forms' },
            {
                title: pageProps.form?.name ?? '',
                href: `/forms/${pageProps.form?.slug ?? ''}`,
            },
            { title: 'Editar', href: '' },
        ],
    }),
});

let nextLocalId = 1;

const fields = ref<FieldRow[]>(
    (props.form.fields || []).map((f) => ({
        _id: nextLocalId++,
        id: f.id,
        type: f.type,
        name: f.name,
        label: f.label,
        placeholder: f.placeholder || '',
        is_required: f.is_required,
        options: Array.isArray(f.options) ? f.options.join('\n') : '',
    })),
);

if (fields.value.length === 0) {
    fields.value = [
        {
            _id: nextLocalId++,
            type: 'text',
            name: '',
            label: '',
            placeholder: '',
            is_required: false,
            options: '',
        },
    ];
}

const form = useForm({
    name: props.form.name,
    description: props.form.description || '',
    is_active: props.form.is_active,
});

function addField() {
    fields.value.push({
        _id: nextLocalId++,
        type: 'text',
        name: '',
        label: '',
        placeholder: '',
        is_required: false,
        options: '',
    });
}

function removeField(_id: number) {
    if (fields.value.length > 1) {
        fields.value = fields.value.filter((f) => f._id !== _id);
    }
}

function needsOptions(type: string): boolean {
    return ['select', 'radio', 'checkbox'].includes(type);
}

function submit() {
    const formData = {
        ...form.data(),
        fields: fields.value.map((f) => ({
            id: f.id,
            type: f.type,
            name: f.name,
            label: f.label,
            placeholder: f.placeholder,
            is_required: f.is_required,
            options:
                f.type === 'select' || f.type === 'radio'
                    ? f.options
                          .split('\n')
                          .map((o) => o.trim())
                          .filter((o) => o)
                    : [],
        })),
    };

    form.put(`/forms/${props.form.slug}`, {
        data: formData,
    });
}
</script>

<template>
    <Head :title="`Editar ${form.name}`" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center gap-4">
            <Link :href="`/forms/${form.slug}`">
                <Button variant="ghost" size="icon">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
            </Link>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Editar Formulário
                </h1>
                <p class="text-sm text-muted-foreground">
                    Atualize os dados do formulário.
                </p>
            </div>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <Card class="max-w-3xl">
                <CardHeader>
                    <CardTitle>Informações Básicas</CardTitle>
                    <CardDescription
                        >Dados gerais do formulário.</CardDescription
                    >
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Nome *</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Nome do formulário"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Descrição</Label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="Descrição opcional do formulário..."
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="flex items-center gap-3 space-y-0">
                        <Checkbox
                            id="is_active"
                            v-model="form.is_active"
                            :checked="form.is_active"
                        />
                        <Label for="is_active" class="!mt-0"
                            >Formulário ativo</Label
                        >
                    </div>
                </CardContent>
            </Card>

            <Card class="max-w-3xl">
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle>Campos do Formulário</CardTitle>
                        <CardDescription
                            >Adicione e configure os campos do
                            formulário.</CardDescription
                        >
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="addField"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Adicionar Campo
                    </Button>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div
                        v-for="field in fields"
                        :key="field._id"
                        class="relative rounded-lg border bg-card p-4"
                    >
                        <div class="flex items-start gap-3">
                            <div class="mt-2 cursor-grab text-muted-foreground">
                                <GripVertical class="h-4 w-4" />
                            </div>
                            <div class="flex-1 space-y-4">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label>Tipo de Campo</Label>
                                        <Select v-model="field.type">
                                            <SelectTrigger>
                                                <SelectValue
                                                    placeholder="Selecione um tipo"
                                                />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="type in fieldTypes"
                                                    :key="type"
                                                    :value="type"
                                                >
                                                    {{
                                                        fieldTypeLabels[type] ||
                                                        type
                                                    }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="space-y-2">
                                        <Label :for="`field-name-${field._id}`"
                                            >Name (ID)</Label
                                        >
                                        <Input
                                            :id="`field-name-${field._id}`"
                                            v-model="field.name"
                                            placeholder="nome_do_campo"
                                        />
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label :for="`field-label-${field._id}`"
                                            >Rótulo</Label
                                        >
                                        <Input
                                            :id="`field-label-${field._id}`"
                                            v-model="field.label"
                                            placeholder="Seu nome completo"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label
                                            :for="`field-placeholder-${field._id}`"
                                            >Placeholder</Label
                                        >
                                        <Input
                                            :id="`field-placeholder-${field._id}`"
                                            v-model="field.placeholder"
                                            placeholder="Digite aqui..."
                                        />
                                    </div>
                                </div>

                                <div
                                    v-if="needsOptions(field.type)"
                                    class="space-y-2"
                                >
                                    <Label :for="`field-options-${field._id}`"
                                        >Opções (uma por linha)</Label
                                    >
                                    <textarea
                                        :id="`field-options-${field._id}`"
                                        v-model="field.options"
                                        class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                        placeholder="Opção 1&#10;Opção 2&#10;Opção 3"
                                    />
                                </div>

                                <div class="flex items-center justify-between">
                                    <div
                                        class="flex items-center gap-3 space-y-0"
                                    >
                                        <Checkbox
                                            :id="`field-required-${field._id}`"
                                            v-model="field.is_required"
                                            :checked="field.is_required"
                                        />
                                        <Label
                                            :for="`field-required-${field._id}`"
                                            class="!mt-0"
                                            >Campo obrigatório</Label
                                        >
                                    </div>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="text-destructive"
                                        :disabled="fields.length <= 1"
                                        @click="removeField(field._id)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="max-w-3xl">
                <CardFooter class="flex justify-between">
                    <Link :href="`/forms/${form.slug}`">
                        <Button variant="outline" type="button"
                            >Cancelar</Button
                        >
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        {{
                            form.processing
                                ? 'Salvando...'
                                : 'Salvar Alterações'
                        }}
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </div>
</template>
