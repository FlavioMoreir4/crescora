<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
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
import { index, show, update } from '@/routes/leads';

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

interface Lead {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    document: string | null;
    source: string | null;
    data: Record<string, unknown> | null;
    status: string;
    unit_id: number | null;
    notes: string | null;
    owner: MemberOption | null;
}

interface DataEntry {
    _id: number;
    key: string;
    value: string;
}

const props = defineProps<{
    lead: Lead;
    statuses: StatusOption[];
    units: UnitOption[];
    members: MemberOption[];
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

let nextDataEntryId = 1;

const dataEntries = ref<DataEntry[]>(
    Object.entries(props.lead.data ?? {}).length > 0
        ? Object.entries(props.lead.data ?? {}).map(([key, value]) => ({
              _id: nextDataEntryId++,
              key,
              value:
                  value === null || value === undefined
                      ? ''
                      : Array.isArray(value)
                        ? value.map((item) => String(item)).join(', ')
                        : String(value),
          }))
        : [
              {
                  _id: nextDataEntryId++,
                  key: '',
                  value: '',
              },
          ],
);

const form = useForm({
    name: props.lead.name,
    email: props.lead.email || '',
    phone: props.lead.phone || '',
    document: props.lead.document || '',
    status: props.lead.status,
    unit_id: (props.lead.unit_id ?? '') as string | number,
    owner_id: (props.lead.owner?.id ?? '') as string | number,
    notes: props.lead.notes || '',
});

function addDataEntry(): void {
    dataEntries.value.push({
        _id: nextDataEntryId++,
        key: '',
        value: '',
    });
}

function removeDataEntry(id: number): void {
    if (dataEntries.value.length > 1) {
        dataEntries.value = dataEntries.value.filter((entry) => entry._id !== id);
    }
}

function buildLeadData(): Record<string, string> {
    return Object.fromEntries(
        dataEntries.value
            .filter((entry) => entry.key.trim() !== '')
            .map((entry) => [entry.key.trim(), entry.value]),
    );
}

function submit() {
    form
        .transform((data) => ({
            ...data,
            data: buildLeadData(),
        }))
        .put(update.url(props.lead.id), {
            preserveScroll: true,
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

                    <div class="space-y-2">
                        <Label for="document">Documento</Label>
                        <Input
                            id="document"
                            v-model="form.document"
                            placeholder="CPF ou CNPJ"
                        />
                        <InputError :message="form.errors.document" />
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

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle>Dados adicionais</CardTitle>
                                <CardDescription>
                                    Campos dinâmicos captados pelo formulário ou
                                    adicionados manualmente.
                                </CardDescription>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="addDataEntry"
                            >
                                <Plus class="mr-2 h-4 w-4" />
                                Adicionar campo
                            </Button>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div
                                v-for="entry in dataEntries"
                                :key="entry._id"
                                class="grid gap-3 rounded-lg border p-4 md:grid-cols-[1fr_1.5fr_auto]"
                            >
                                <div class="space-y-2">
                                    <Label :for="`data-key-${entry._id}`">
                                        Chave
                                    </Label>
                                    <Input
                                        :id="`data-key-${entry._id}`"
                                        v-model="entry.key"
                                        placeholder="cidade"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label :for="`data-value-${entry._id}`">
                                        Valor
                                    </Label>
                                    <Input
                                        :id="`data-value-${entry._id}`"
                                        v-model="entry.value"
                                        placeholder="São Paulo"
                                    />
                                </div>
                                <div class="flex items-end">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="text-destructive"
                                        :disabled="dataEntries.length <= 1"
                                        @click="removeDataEntry(entry._id)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
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
