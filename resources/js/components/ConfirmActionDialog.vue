<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

interface Props {
    open: boolean;
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
}

const props = withDefaults(defineProps<Props>(), {
    description: 'Essa ação não pode ser desfeita.',
    confirmLabel: 'Continuar',
    cancelLabel: 'Cancelar',
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
}>();

function handleConfirm(): void {
    emit('confirm');
    emit('update:open', false);
}
</script>

<template>
    <AlertDialog
        :open="props.open"
        @update:open="emit('update:open', $event)"
    >
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ props.title }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ props.description }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="emit('update:open', false)">
                    {{ props.cancelLabel }}
                </AlertDialogCancel>
                <AlertDialogAction
                    class="bg-destructive text-destructive-foreground hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:bg-destructive/60 dark:hover:bg-destructive/70 dark:focus-visible:ring-destructive/40"
                    @click="handleConfirm"
                >
                    {{ props.confirmLabel }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
