import { cn } from '@/lib/utils';
import { UploadCloud, X } from 'lucide-react';
import React, { useCallback, useEffect, useRef, useState } from 'react';

interface ImageUploadProps {
    onChange: (files: File | File[] | null) => void;
    value?: string | null; // Only for existing saved image URL (from server)
    multiple?: boolean;
    accept?: string;
    className?: string;
}

export default function ImageUpload({ onChange, value, multiple = false, accept = 'image/*', className }: ImageUploadProps) {
    // Local state: stores selected File(s) for immediate preview
    const [selectedFiles, setSelectedFiles] = useState<File[]>([]);
    // Whether the user has explicitly cleared the existing image
    const [existingCleared, setExistingCleared] = useState(false);
    const [isDragging, setIsDragging] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const inputRef = useRef<HTMLInputElement>(null);

    // Reset local state when value resets to a new URL (e.g. after form success)
    useEffect(() => {
        setSelectedFiles([]);
        setExistingCleared(false);
    }, [value]);

    // Build the list of previews to render
    // Priority: selected local File objects first, then fallback to existing URL
    const previews: Array<{ src: string; isFile: boolean; fileObj?: File }> = [];

    if (selectedFiles.length > 0) {
        selectedFiles.forEach((file) => {
            previews.push({ src: URL.createObjectURL(file), isFile: true, fileObj: file });
        });
    } else if (value && !existingCleared) {
        // Normalise URL: ensure it starts with / or http
        const normalisedUrl = value.startsWith('http') || value.startsWith('/') ? value : `/${value}`;
        previews.push({ src: normalisedUrl, isFile: false });
    }

    const handleFiles = useCallback(
        (fileList: FileList | File[]) => {
            setError(null);
            const filesArray = Array.from(fileList);

            const invalidType = filesArray.find((f) => !f.type.startsWith('image/'));
            if (invalidType) {
                setError('Please upload only image files.');
                return;
            }

            if (multiple) {
                const merged = [...selectedFiles, ...filesArray];
                setSelectedFiles(merged);
                onChange(merged);
            } else {
                setSelectedFiles([filesArray[0]]);
                setExistingCleared(false);
                onChange(filesArray[0]);
            }

            // Reset the native input so the same file can be re-selected if removed
            if (inputRef.current) inputRef.current.value = '';
        },
        [multiple, selectedFiles, onChange],
    );

    const removeFile = (index: number) => {
        if (!multiple) {
            // If removing a local File preview
            if (selectedFiles.length > 0) {
                setSelectedFiles([]);
            } else {
                // Removing the existing saved image
                setExistingCleared(true);
            }
            onChange(null);
            if (inputRef.current) inputRef.current.value = '';
            return;
        }

        const newFiles = selectedFiles.filter((_, i) => i !== index);
        setSelectedFiles(newFiles);
        onChange(newFiles.length > 0 ? newFiles : null);
    };

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

    return (
        <div className={cn('w-full transition-all', !multiple ? 'h-[200px]' : 'min-h-[200px]', className)}>
            <div
                className={cn(
                    'relative flex h-full w-full cursor-pointer flex-col items-center justify-center overflow-hidden rounded-lg border-2 border-dashed transition-colors',
                    isDragging ? 'border-primary bg-primary/10' : 'border-border hover:border-primary/50 hover:bg-muted/50',
                    'p-2',
                )}
                onDragOver={onDragOver}
                onDragLeave={onDragLeave}
                onDrop={onDrop}
            >
                {/* Invisible file input — sits on top but only when no preview */}
                {previews.length === 0 && (
                    <input
                        ref={inputRef}
                        type="file"
                        accept={accept}
                        multiple={multiple}
                        onChange={(e) => e.target.files && handleFiles(e.target.files)}
                        className="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                    />
                )}

                {previews.length === 0 ? (
                    <div className="flex h-full w-full flex-col items-center justify-center space-y-2 py-4">
                        <UploadCloud className="text-muted-foreground h-10 w-10" />
                        <p className="text-muted-foreground text-sm">
                            <span className="text-primary font-semibold">Click to upload</span> or drag and drop
                        </p>
                        <p className="text-muted-foreground mt-2 text-xs">SVG, PNG, JPG or GIF</p>
                    </div>
                ) : (
                    <div className={`grid h-full w-full gap-4 ${multiple ? 'grid-cols-2 md:grid-cols-3' : 'grid-cols-1'}`}>
                        {previews.map((preview, index) => (
                            <div
                                key={index}
                                className={cn(
                                    'bg-muted group relative flex items-center justify-center overflow-hidden rounded-md',
                                    multiple ? 'max-h-[180px]' : 'h-full w-full',
                                )}
                            >
                                <img src={preview.src} alt="Preview" className="h-full w-full object-contain" />

                                {/* Always-visible remove button (no hover needed) */}
                                <button
                                    type="button"
                                    onClick={(e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        removeFile(index);
                                    }}
                                    className="bg-destructive text-destructive-foreground hover:bg-destructive/90 absolute right-2 top-2 z-20 rounded-full p-1.5 shadow-md transition-colors"
                                >
                                    <X className="h-4 w-4 text-white" />
                                </button>
                            </div>
                        ))}

                        {/* Add more slot for multiple mode */}
                        {multiple && (
                            <div className="border-border hover:border-primary/50 hover:bg-muted/50 relative flex max-h-[180px] flex-col items-center justify-center space-y-2 rounded-md border-2 border-dashed py-2">
                                <input
                                    ref={inputRef}
                                    type="file"
                                    accept={accept}
                                    multiple
                                    onChange={(e) => e.target.files && handleFiles(e.target.files)}
                                    className="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                                />
                                <UploadCloud className="text-muted-foreground h-6 w-6" />
                                <p className="text-muted-foreground text-xs">Add more</p>
                            </div>
                        )}

                        {/* Click to replace in single mode */}
                        {!multiple && (
                            <input
                                ref={inputRef}
                                type="file"
                                accept={accept}
                                onChange={(e) => e.target.files && handleFiles(e.target.files)}
                                className="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                                style={{ display: 'none' }}
                            />
                        )}
                    </div>
                )}
            </div>

            {error && <p className="text-destructive mt-2 text-sm font-medium">{error}</p>}
        </div>
    );
}
