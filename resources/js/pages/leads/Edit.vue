<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { index, show, edit, update } from '@/routes/leads';
import { useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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

interface StatusOption {
    value: string;
    label: string;
}

interface UnitOption {
    id: number;
    name: string;
}

interface Lead {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    status: string;
    unit_id: number | null;
    notes: string | null;
}

const props = defineProps<{
    lead: Lead;
    statuses: StatusOption[];
    units: UnitOption[];
}>();

defineOptions({
    layout: (pageProps: { lead?: { name: string; id: number } }) => ({
        breadcrumbs: [
            { title: 'Leads', href: index.url() },
            {
                title: pageProps.lead?.name ?? '',
                href: show.url(pageProps.lead?.id ?? 0),
            },
            { title: 'Editar', href: '' },
        ],
    }),
});

const form = useForm({
    name: props.lead.name,
    email: props.lead.email || '',
    phone: props.lead.phone || '',
    status: props.lead.status,
    unit_id: (props.lead.unit_id ?? '') as string | number,
    notes: props.lead.notes || '',
});

function submit() {
    form.put(update.url(props.lead.id), {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head :title="`Editar ${lead.name}`" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center gap-4">
            <Link :href="show.url(lead.id)">
                <Button variant="ghost" size="icon">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
            </Link>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Editar Lead</h1>
                <p class="text-sm text-muted-foreground">
                    Altere os dados do lead.
                </p>
            </div>
        </div>

        <Card class="max-w-2xl">
            <form @submit.prevent="submit">
                <CardHeader>
                    <CardTitle>Informações do Lead</CardTitle>
                    <CardDescription
                        >Atualize os dados do lead.</CardDescription
                    >
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Nome *</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Nome completo"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="email">E-mail</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="email@exemplo.com"
                            />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div class="space-y-2">
                            <Label for="phone">Telefone</Label>
                            <Input
                                id="phone"
                                v-model="form.phone"
                                placeholder="(11) 99999-9999"
                            />
                            <InputError :message="form.errors.phone" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger id="status">
                                    <SelectValue
                                        placeholder="Selecione um status"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="s in statuses"
                                        :key="s.value"
                                        :value="s.value"
                                    >
                                        {{ s.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.status" />
                        </div>
                        <div class="space-y-2">
                            <Label for="unit_id">Unidade</Label>
                            <Select v-model="form.unit_id">
                                <SelectTrigger id="unit_id">
                                    <SelectValue
                                        placeholder="Selecione uma unidade"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null"
                                        >Sem unidade</SelectItem
                                    >
                                    <SelectItem
                                        v-for="u in units"
                                        :key="u.id"
                                        :value="u.id"
                                    >
                                        {{ u.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.unit_id" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="notes">Observações</Label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="Observações sobre o lead..."
                        />
                        <InputError :message="form.errors.notes" />
                    </div>
                </CardContent>
                <CardFooter class="flex justify-between">
                    <Link :href="show.url(lead.id)">
                        <Button variant="outline" type="button"
                            >Cancelar</Button
                        >
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Salvando...' : 'Salvar' }}
                    </Button>
                </CardFooter>
            </form>
        </Card>
    </div>
</template>
