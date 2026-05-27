<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import PublicLayout from '@/layouts/PublicLayout.vue';

interface Field {
    id: number;
    type: string;
    name: string;
    label: string;
    placeholder: string | null;
    options: string[] | null;
    is_required: boolean;
    order: number;
}

interface Team {
    name: string;
    slug: string;
}

interface Unit {
    name: string;
    slug: string;
}

interface FormModel {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    fields: Field[];
}

type FormResponses = Record<string, any>;

const props = defineProps<{
    team: Team;
    unit: Unit;
    form: FormModel;
    submitUrl: string;
}>();

defineOptions({
    layout: PublicLayout,
});

const initialData = computed<FormResponses>(() =>
    Object.fromEntries(
        props.form.fields.map((field) => [
            field.name,
            field.type === 'checkbox' ? false : '',
        ]),
    ),
);

const submissionForm = useForm<{ responses: FormResponses }>({
    responses: initialData.value as FormResponses,
});

function submitForm(): void {
    submissionForm.transform((data) => ({
        data: data.responses,
    })).post(props.submitUrl, {
        preserveScroll: true,
        onSuccess: () => {
            submissionForm.reset();
        },
    });
}

function fieldError(name: string): string | undefined {
    return (
        submissionForm.errors as Record<string, string | undefined>
    )[`data.${name}`];
}

function inputType(type: string): string {
    return (
        {
            email: 'email',
            tel: 'tel',
            number: 'number',
            date: 'date',
            cpf: 'text',
            cnpj: 'text',
            cep: 'text',
            hidden: 'hidden',
        }[type] ?? 'text'
    );
}
</script>

<template>
    <Head :title="form.name" />

    <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(15,23,42,0.08),_transparent_40%),linear-gradient(180deg,_#f8fafc_0%,_#eef2ff_100%)] px-6 py-12 text-slate-950 dark:bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.08),_transparent_35%),linear-gradient(180deg,_#020617_0%,_#0f172a_100%)] dark:text-slate-50">
        <div class="mx-auto grid min-h-screen max-w-6xl items-center gap-10 lg:grid-cols-[1.05fr_0.95fr]">
            <section class="space-y-6">
                <div class="flex items-center gap-2">
                    <Badge variant="outline" class="border-slate-300/70 bg-white/70 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-slate-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
                        {{ team.name }}
                    </Badge>
                    <Badge variant="secondary" class="px-3 py-1 text-xs font-medium">
                        {{ unit.name }}
                    </Badge>
                </div>

                <div class="space-y-4">
                    <p class="text-sm font-medium uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">
                        Captação pública
                    </p>
                    <h1 class="max-w-2xl text-4xl font-semibold tracking-tight text-balance sm:text-5xl">
                        {{ form.name }}
                    </h1>
                    <p class="max-w-xl text-base leading-7 text-slate-600 dark:text-slate-300">
                        {{ form.description || 'Envie seus dados e o time responsável entra em contato.' }}
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200/80 bg-white/75 p-4 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-950/50">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400">
                            Etapa
                        </p>
                        <p class="mt-2 text-sm font-medium text-slate-900 dark:text-slate-100">
                            Formulário dinâmico
                        </p>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-white/75 p-4 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-950/50">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400">
                            Resultado
                        </p>
                        <p class="mt-2 text-sm font-medium text-slate-900 dark:text-slate-100">
                            Lead criado automaticamente
                        </p>
                    </div>
                </div>
            </section>

            <Card class="border-slate-200/80 bg-white/90 shadow-2xl shadow-slate-900/5 backdrop-blur dark:border-slate-800 dark:bg-slate-950/80">
                <CardHeader class="space-y-2 border-b border-slate-200/80 pb-6 dark:border-slate-800">
                    <CardTitle class="text-2xl">Preencha seus dados</CardTitle>
                    <p class="text-sm text-muted-foreground">
                        Quanto mais completo, mais rápido o retorno.
                    </p>
                </CardHeader>

                <CardContent class="pt-6">
                    <form class="space-y-5" @submit.prevent="submitForm">
                        <div
                            v-for="field in form.fields"
                            :key="field.id"
                            class="space-y-2"
                        >
                            <Label :for="`field-${field.id}`" class="flex items-center gap-1">
                                <span>{{ field.label }}</span>
                                <span
                                    v-if="field.is_required"
                                    class="text-destructive"
                                >
                                    *
                                </span>
                            </Label>

                            <Textarea
                                v-if="field.type === 'textarea'"
                                :id="`field-${field.id}`"
                                v-model="submissionForm.responses[field.name]"
                                :placeholder="field.placeholder || field.label"
                            />

                            <Select
                                v-else-if="
                                    ['select', 'radio'].includes(field.type)
                                    && field.options?.length
                                "
                                v-model="submissionForm.responses[field.name]"
                            >
                                <SelectTrigger :id="`field-${field.id}`">
                                    <SelectValue
                                        :placeholder="
                                            field.placeholder || 'Selecione uma opção'
                                        "
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="option in field.options"
                                        :key="option"
                                        :value="option"
                                    >
                                        {{ option }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <div
                                v-else-if="field.type === 'checkbox'"
                                class="flex items-center gap-3 rounded-lg border border-slate-200/70 px-3 py-3 dark:border-slate-800"
                            >
                                <input
                                    :id="`field-${field.id}`"
                                    v-model="submissionForm.responses[field.name]"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-slate-300 text-slate-950 focus:ring-slate-400 dark:border-slate-700 dark:bg-slate-900"
                                />
                                <Label :for="`field-${field.id}`" class="cursor-pointer font-normal">
                                    {{ field.placeholder || 'Marque para continuar' }}
                                </Label>
                            </div>

                            <Input
                                v-else
                                :id="`field-${field.id}`"
                                v-model="submissionForm.responses[field.name]"
                                :type="inputType(field.type)"
                                :placeholder="field.placeholder || field.label"
                                :autocomplete="
                                    field.type === 'email'
                                        ? 'email'
                                        : field.type === 'tel'
                                            ? 'tel'
                                            : 'off'
                                "
                            />

                            <InputError :message="fieldError(field.name)" />
                        </div>

                        <div class="flex items-center justify-between gap-4 pt-2">
                            <p class="text-xs leading-5 text-muted-foreground">
                                Ao enviar, seus dados serão encaminhados para o time da unidade.
                            </p>
                            <Button type="submit" :disabled="submissionForm.processing" class="shrink-0">
                                {{ submissionForm.processing ? 'Enviando...' : 'Enviar' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
