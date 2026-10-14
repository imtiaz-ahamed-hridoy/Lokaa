import { Head, useForm, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AuthLayout from '@/layouts/auth-layout';

export default function VerifyEmail({ status }: { status?: string }) {
    const { settings } = usePage().props as any;
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    return (
        <AuthLayout title="" description="">
            <Head title="Email verification" />

            <div className="flex flex-col gap-6">
                <Card className="overflow-hidden rounded-3xl border-none bg-[#F8F8F8] shadow-none">
                    <CardHeader className="flex flex-col items-center gap-2 pb-2 pt-8">
                        {settings?.logo ? (
                            <img src={settings.logo} alt="Logo" className="h-12 w-auto object-contain" />
                        ) : (
                            <CardTitle className="text-4xl font-bold text-[#037FFF]">{settings?.system_name || 'Verify Email'}</CardTitle>
                        )}
                        <CardDescription className="mt-2 text-wrap text-center text-gray-500">
                            Please verify your email address <br /> by clicking on the link we just emailed to you
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="px-8 pb-8 pt-0">
                        {status === 'verification-link-sent' && (
                            <div className="mb-4 text-center text-sm font-medium text-green-600">
                                A new verification link has been sent to your email.
                            </div>
                        )}

                        <form onSubmit={submit} className="flex flex-col gap-6">
                            <Button
                                type="submit"
                                className="h-11 w-full rounded-3xl bg-[#037FFF] font-bold text-white transition-all hover:bg-[#026BD8] active:scale-95"
                                disabled={processing}
                            >
                                {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                Resend verification email
                            </Button>

                            <div className="text-center text-sm">
                                <TextLink href={route('logout')} method="post" className="text-[#037FFF] hover:text-[#026BD8]">
                                    Log out
                                </TextLink>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AuthLayout>
    );
}
