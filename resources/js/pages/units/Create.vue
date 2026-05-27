<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { index, create, store } from '@/routes/units';
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
import InputError from '@/components/InputError.vue';
import { Switch } from '@/components/ui/switch';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Unidades', href: index.url() },
            { title: 'Nova Unidade', href: create.url() },
        ],
    },
});

const form = useForm({
    name: '',
    slug: '',
    description: '',
    phone: '',
    email: '',
    address: '',
    city: '',
    state: '',
    zip: '',
    is_active: true,
});

function submit() {
    form.post(store.url(), {
        onSuccess: () => form.reset(),
    });
}

function generateSlug(name: string) {
    form.slug = name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

// buscar CEP
async function fetchAddress(cep: string) {
    cep = cep.replace(/\D/g, '');

    try {
        if (cep.length !== 8) return;

        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);

        if (!response.ok) return;

        const data = await response.json();
        form.address = data.logradouro;
        form.city = data.localidade;
        form.state = data.uf;
    } catch (error) {
        form.address = '';
        form.city = '';
        form.state = '';
    }
}
</script>

<template>
    <Head title="Nova Unidade" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center gap-4">
            <Link :href="index.url()">
                <Button variant="ghost" size="icon">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
            </Link>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Nova Unidade</h1>
                <p class="text-sm text-muted-foreground">
                    Cadastre uma nova unidade ou franquia.
                </p>
            </div>
        </div>

        <Card class="max-w-2xl">
            <form @submit.prevent="submit">
                <CardHeader>
                    <CardTitle>Informações da Unidade</CardTitle>
                    <CardDescription
                        >Preencha os dados da unidade.</CardDescription
                    >
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Nome *</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Nome da unidade"
                            @input="generateSlug(form.name)"
                        />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="space-y-2">
                        <Label for="slug">Slug *</Label>
                        <Input
                            id="slug"
                            v-model="form.slug"
                            placeholder="nome-da-unidade"
                        />
                        <InputError :message="form.errors.slug" />
                    </div>
                    <div class="space-y-2">
                        <Label for="description">Descrição</Label>
                        <Input
                            id="description"
                            v-model="form.description"
                            placeholder="Descrição da unidade"
                        />
                        <InputError :message="form.errors.description" />
                    </div>
                    <div class="space-y-2">
                        <Label for="phone">Telefone</Label>
                        <Input
                            id="phone"
                            v-model="form.phone"
                            placeholder="Telefone da unidade"
                        />
                        <InputError :message="form.errors.phone" />
                    </div>
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            placeholder="Email da unidade"
                        />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div class="space-y-2">
                        <Label for="zip">CEP</Label>
                        <Input
                            id="zip"
                            v-model="form.zip"
                            placeholder="CEP da unidade"
                            @blur="fetchAddress(form.zip)"
                        />
                        <InputError :message="form.errors.zip" />
                    </div>
                    <div class="space-y-2">
                        <Label for="address">Endereço</Label>
                        <Input
                            id="address"
                            v-model="form.address"
                            placeholder="Endereço da unidade"
                        />
                        <InputError :message="form.errors.address" />
                    </div>
                    <div class="space-y-2">
                        <Label for="city">Cidade</Label>
                        <Input
                            id="city"
                            v-model="form.city"
                            placeholder="Cidade da unidade"
                        />
                        <InputError :message="form.errors.city" />
                    </div>
                    <div class="space-y-2">
                        <Label for="state">Estado</Label>
                        <Input
                            id="state"
                            v-model="form.state"
                            placeholder="Estado da unidade"
                        />
                        <InputError :message="form.errors.state" />
                    </div>

                    <div class="flex items-center space-y-2 space-x-2">
                        <Switch
                            id="is_active"
                            :modelValue="form.is_active"
                            @update:modelValue="form.is_active = $event"
                        />
                        <Label for="is_active">Ativo</Label>
                        <InputError :message="form.errors.is_active" />
                    </div>
                </CardContent>
                <CardFooter class="flex justify-between py-2">
                    <Link :href="index.url()">
                        <Button variant="outline" type="button"
                            >Cancelar</Button
                        >
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Salvando...' : 'Salvar Unidade' }}
                    </Button>
                </CardFooter>
            </form>
        </Card>
    </div>
</template>
