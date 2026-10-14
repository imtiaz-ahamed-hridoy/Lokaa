'use client';

import { AlertDialog, Button } from '@heroui/react';
import { Trash } from 'lucide-react';
import { ReactNode } from 'react';

interface ConfirmDialogProps {
    title?: string;
    description?: string;
    onConfirm: () => void;
    trigger: ReactNode;
    icon?: ReactNode;
    confirmText?: string;
    confirmColor?: 'danger' | 'primary' | 'secondary' | 'tertiary' | 'danger-soft' | 'ghost' | 'outline';
    onOpenChange?: (isOpen: boolean) => void;
}

export function ConfirmDialog({
    title = 'Delete this item?',
    description = 'Are you sure you want to delete this item? This action cannot be undone.',
    onConfirm,
    trigger,
    icon = <Trash className="size-5" />,
    confirmText = 'Delete',
    confirmColor = 'danger',
    onOpenChange,
}: ConfirmDialogProps) {
    return (
        <AlertDialog onOpenChange={onOpenChange}>
            {trigger}
            <AlertDialog.Backdrop>
                <AlertDialog.Container>
                    <AlertDialog.Dialog className="sm:max-w-[400px]">
                        <AlertDialog.CloseTrigger className="text-gray-900 dark:text-gray-100" />
                        <AlertDialog.Header>
                            <AlertDialog.Icon status={confirmColor === 'danger' ? 'danger' : undefined}>{icon}</AlertDialog.Icon>
                            <AlertDialog.Heading>{title}</AlertDialog.Heading>
                        </AlertDialog.Header>
                        <AlertDialog.Body>
                            <p className="text-gray-900 dark:text-gray-100">{description}</p>
                        </AlertDialog.Body>
                        <AlertDialog.Footer>
                            <Button slot="close" variant="tertiary">
                                Cancel
                            </Button>
                            <Button slot="close" variant={confirmColor} onPress={onConfirm}>
                                {confirmText}
                            </Button>
                        </AlertDialog.Footer>
                    </AlertDialog.Dialog>
                </AlertDialog.Container>
            </AlertDialog.Backdrop>
        </AlertDialog>
    );
}
