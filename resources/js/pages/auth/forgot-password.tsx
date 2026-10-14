import { Head, useForm, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

export default function ForgotPassword({ status }: { status?: string }) {
    const { settings } = usePage().props as any;
    const { data, setData, post, processing, errors } = useForm<Required<{ email: string }>>({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <AuthLayout title="" description="">
            <Head title="Forgot password" />

            <div className="flex flex-col gap-6">
                <Card className="overflow-hidden rounded-3xl border-none bg-[#F8F8F8] shadow-none">
                    <CardHeader className="flex flex-col items-center gap-2 pb-2 pt-8">
                        {settings?.logo ? (
                            <img src={settings.logo} alt="Logo" className="h-12 w-auto object-contain" />
                        ) : (
                            <CardTitle className="text-4xl font-bold text-[#037FFF]">{settings?.system_name || 'Forgot Password'}</CardTitle>
                        )}
                        <CardDescription className="mt-2 text-wrap text-center text-gray-500">
                            Enter your email to receive a <br /> password reset link
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="px-8 pb-8 pt-0">
                        {status && <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>}

                        <form className="flex flex-col gap-6" onSubmit={submit}>
                            <div className="grid gap-6">
                                <div className="grid gap-2">
                                    <Label htmlFor="email" className="font-medium text-black/70">
                                        Email address
                                    </Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        autoComplete="off"
                                        value={data.email}
                                        autoFocus
                                        onChange={(e) => setData('email', e.target.value)}
                                        placeholder="email@example.com"
                                        className="h-11 border-0 bg-white text-black/70 shadow-none focus-visible:ring-white/10"
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <Button
                                    type="submit"
                                    className="mt-2 h-11 w-full rounded-3xl bg-[#037FFF] font-bold text-white transition-all hover:bg-[#026BD8] active:scale-95"
                                    disabled={processing}
                                >
                                    {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                    Email reset link
                                </Button>
                            </div>

                            <div className="text-center text-sm text-black/70">
                                <span>Or, return to</span>{' '}
                                <TextLink href={route('login')} className="text-[#037FFF] hover:text-[#026BD8]">
                                    log in
                                </TextLink>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AuthLayout>
    );
}
