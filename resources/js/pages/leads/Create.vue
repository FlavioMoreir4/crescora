<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
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
import { index, create, store } from '@/routes/leads';

interface StatusOption {
    value: string;
    label: string;
}

interface UnitOption {
    id: number;
    name: string;
}

interface MemberOption {
    id: number;
    name: string;
}

const props = defineProps<{
    statuses: StatusOption[];
    units: UnitOption[];
    members: MemberOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Leads', href: index.url() },
            { title: 'Novo Lead', href: create.url() },
        ],
    },
});

const form = useForm({
    name: '',
    email: '',
    phone: '',
    status: props.statuses[0]?.value || 'new',
    unit_id: '' as string | number,
    owner_id: '' as string | number,
    notes: '',
});

function submit() {
    form.post(store.url(), {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Novo Lead" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center gap-4">
            <Link :href="index.url()">
                <Button variant="ghost" size="icon">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
            </Link>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Novo Lead</h1>
                <p class="text-sm text-muted-foreground">
                    Cadastre um novo lead no sistema.
                </p>
            </div>
        </div>

        <Card class="max-w-2xl">
            <form @submit.prevent="submit">
                <CardHeader>
                    <CardTitle>Informações do Lead</CardTitle>
                    <CardDescription
                        >Preencha os dados do novo lead.</CardDescription
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
                        <Label for="owner_id">Responsável</Label>
                        <Select v-model="form.owner_id">
                            <SelectTrigger id="owner_id">
                                <SelectValue
                                    placeholder="Selecione um responsável"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="null">
                                    Sem responsável
                                </SelectItem>
                                <SelectItem
                                    v-for="member in members"
                                    :key="member.id"
                                    :value="member.id"
                                >
                                    {{ member.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.owner_id" />
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
                    <Link :href="index.url()">
                        <Button variant="outline" type="button"
                            >Cancelar</Button
                        >
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Salvando...' : 'Salvar Lead' }}
                    </Button>
                </CardFooter>
            </form>
        </Card>
    </div>
</template>
