import { cn } from '@/lib/utils';
import { File as FileIcon, FileText, UploadCloud, X } from 'lucide-react';
import React, { useCallback, useEffect, useState } from 'react';

interface FileUploadProps {
    onChange: (file: File | null) => void;
    value?: File | string | null;
    accept?: string;
    className?: string;
    id?: string;
}

export default function FileUpload({ onChange, value, accept = '.pdf', className, id }: FileUploadProps) {
    const [fileName, setFileName] = useState<string | null>(null);
    const [isDragging, setIsDragging] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (!value) {
            setFileName(null);
            return;
        }

        if (typeof value === 'string') {
            // Extract filename from URL
            const parts = value.split('/');
            setFileName(parts[parts.length - 1]);
        } else if (value instanceof File) {
            setFileName(value.name);
        }
    }, [value]);

    const handleFiles = useCallback(
        (selectedFiles: FileList | File[]) => {
            setError(null);
            const filesArray = Array.from(selectedFiles);
            const file = filesArray[0];

            if (!file) return;

            // Simple validation based on 'accept' if provided
            if (accept && accept !== '*') {
                const acceptedTypes = accept.split(',').map((t) => t.trim());
                const fileExtension = '.' + file.name.split('.').pop()?.toLowerCase();
                const fileType = file.type;

                const isValid = acceptedTypes.some((type) => {
                    if (type.startsWith('.')) return type === fileExtension;
                    if (type.endsWith('/*')) return fileType.startsWith(type.replace('/*', ''));
                    return type === fileType;
                });

                if (!isValid) {
                    setError(`Please upload a file of type: ${accept}`);
                    return;
                }
            }

            setFileName(file.name);
            onChange(file);
        },
        [accept, onChange],
    );

    const onDragOver = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(true);
    }, []);

    const onDragLeave = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(false);
    }, []);

    const onDrop = useCallback(
        (e: React.DragEvent) => {
            e.preventDefault();
            setIsDragging(false);
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                handleFiles(e.dataTransfer.files);
            }
        },
        [handleFiles],
    );

    const removeFile = (e: React.MouseEvent) => {
        e.stopPropagation();
        e.preventDefault();
        setFileName(null);
        onChange(null);
    };

    return (
        <div className={cn('h-[150px] w-full transition-all', className)}>
            <div
                className={`relative flex h-full w-full cursor-pointer flex-col items-center justify-center overflow-hidden rounded-lg border-2 border-dashed transition-colors ${isDragging ? 'border-primary bg-primary/10' : 'border-border hover:border-primary/50 hover:bg-muted/50'} ${fileName ? 'bg-muted/30' : ''} `}
                onDragOver={onDragOver}
                onDragLeave={onDragLeave}
                onDrop={onDrop}
            >
                <input
                    id={id}
                    type="file"
                    accept={accept}
                    onChange={(e) => e.target.files && handleFiles(e.target.files)}
                    className="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                />

                {!fileName ? (
                    <div className="flex flex-col items-center justify-center space-y-2 py-4">
                        <UploadCloud className="text-muted-foreground h-8 w-8" />
                        <p className="text-muted-foreground text-sm">
                            <span className="text-primary font-semibold">Click to upload</span> or drag and drop
                        </p>
                        <p className="text-muted-foreground mt-1 text-xs">
                            {accept === '.pdf' ? 'PDF files only' : accept.replace(/\./g, '').toUpperCase()}
                        </p>
                    </div>
                ) : (
                    <div className="flex w-full flex-col items-center justify-center space-y-2 px-4">
                        <div className="bg-background group relative flex w-full max-w-[300px] items-center gap-3 rounded-md border p-3 shadow-sm">
                            <div className="bg-primary/10 rounded p-2">
                                {fileName.toLowerCase().endsWith('.pdf') ? (
                                    <FileText className="text-primary h-6 w-6" />
                                ) : (
                                    <FileIcon className="text-primary h-6 w-6" />
                                )}
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-medium">{fileName}</p>
                                <p className="text-muted-foreground text-xs uppercase">{fileName.split('.').pop()}</p>
                            </div>
                            <button type="button" onClick={removeFile} className="hover:bg-muted rounded-full p-1 transition-colors">
                                <X className="text-muted-foreground hover:text-destructive h-4 w-4" />
                            </button>
                        </div>
                        <p className="text-primary text-xs font-medium">File selected - Click or drag to change</p>
                    </div>
                )}
            </div>

            {error && <p className="text-destructive mt-2 text-sm font-medium">{error}</p>}
        </div>
    );
}
