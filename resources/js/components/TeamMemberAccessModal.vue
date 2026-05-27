<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { update as updateResourceAccess } from '@/routes/teams/members/resources';
import type {
    ResourceAccessLevelOption,
    Team,
    TeamFormOption,
    TeamMember,
    TeamResourceOption,
    TeamResourceType,
} from '@/types';

type ResourceEntry = {
    resource_id: number;
    access_level: '' | ResourceAccessLevelOption['value'];
};

type Props = {
    team: Team;
    member: TeamMember | null;
    units: TeamResourceOption[];
    forms: TeamFormOption[];
    accessLevels: ResourceAccessLevelOption[];
    open: boolean;
};

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    resources: {
        units: [] as ResourceEntry[],
        forms: [] as ResourceEntry[],
    },
});

const resourceAccessIndex = computed(() => {
    const accesses = props.member?.resource_accesses ?? [];

    return accesses.reduce<Record<string, ResourceAccessLevelOption['value']>>(
        (carry, access) => {
            carry[`${access.resource_type}:${access.resource_id}`] =
                access.access_level;

            return carry;
        },
        {},
    );
});

function resourceKey(type: TeamResourceType, id: number): string {
    return `${type}:${id}`;
}

function syncForm(): void {
    form.resources.units = props.units.map((unit) => ({
        resource_id: unit.id,
        access_level:
            resourceAccessIndex.value[resourceKey('unit', unit.id)] ?? '',
    }));

    form.resources.forms = props.forms.map((formItem) => ({
        resource_id: formItem.id,
        access_level:
            resourceAccessIndex.value[resourceKey('form', formItem.id)] ?? '',
    }));

    form.clearErrors();
}

watch(
    () => [props.open, props.member?.id] as const,
    ([open]) => {
        if (open && props.member) {
            syncForm();
        }
    },
    { immediate: true },
);

function handleOpenChange(value: boolean): void {
    emit('update:open', value);

    if (!value) {
        form.reset();
        form.clearErrors();
    }
}

function submit(): void {
    if (!props.member) {
        return;
    }

    form.patch(
        updateResourceAccess.url([props.team.slug, props.member.id]),
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
            },
        },
    );
}
</script>

<template>
    <Dialog :open="open" @update:open="handleOpenChange">
        <DialogContent class="max-h-[85vh] overflow-y-auto sm:max-w-3xl">
            <DialogHeader>
                <DialogTitle>Gerenciar acessos</DialogTitle>
                <DialogDescription>
                    Defina quais unidades e formulários este membro pode ver ou
                    gerenciar.
                </DialogDescription>
            </DialogHeader>

            <div v-if="member" class="space-y-6">
                <div class="rounded-lg border p-4">
                    <p class="text-sm font-medium">{{ member.name }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ member.email }}
                    </p>
                </div>

                <div class="space-y-4">
                    <div>
                        <h3 class="mb-3 text-sm font-semibold">Unidades</h3>
                        <div v-if="units.length > 0" class="space-y-3">
                            <div
                                v-for="(unit, index) in units"
                                :key="unit.id"
                                class="grid gap-2 rounded-lg border p-4 md:grid-cols-[1fr_220px]"
                            >
                                <div>
                                    <Label class="text-sm font-medium">
                                        {{ unit.name }}
                                    </Label>
                                    <p class="text-xs text-muted-foreground">
                                        Slug: {{ unit.slug }}
                                    </p>
                                </div>
                                <Select
                                    v-model="form.resources.units[index].access_level"
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Sem acesso" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="null">
                                            Sem acesso
                                        </SelectItem>
                                        <SelectItem
                                            v-for="level in accessLevels"
                                            :key="level.value"
                                            :value="level.value"
                                        >
                                            {{ level.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">
                            Nenhuma unidade disponível.
                        </p>
                    </div>

                    <div>
                        <h3 class="mb-3 text-sm font-semibold">Formulários</h3>
                        <div v-if="forms.length > 0" class="space-y-3">
                            <div
                                v-for="(formItem, index) in forms"
                                :key="formItem.id"
                                class="grid gap-2 rounded-lg border p-4 md:grid-cols-[1fr_220px]"
                            >
                                <div>
                                    <Label class="text-sm font-medium">
                                        {{ formItem.name }}
                                    </Label>
                                    <p class="text-xs text-muted-foreground">
                                        Slug: {{ formItem.slug }}
                                    </p>
                                </div>
                                <Select
                                    v-model="form.resources.forms[index].access_level"
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Sem acesso" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="null">
                                            Sem acesso
                                        </SelectItem>
                                        <SelectItem
                                            v-for="level in accessLevels"
                                            :key="level.value"
                                            :value="level.value"
                                        >
                                            {{ level.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">
                            Nenhum formulário disponível.
                        </p>
                    </div>
                </div>
            </div>

            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button variant="secondary" type="button">Cancelar</Button>
                </DialogClose>
                <Button type="button" :disabled="form.processing" @click="submit">
                    Salvar acessos
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
