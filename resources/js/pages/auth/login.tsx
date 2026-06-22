import { Head } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { api, setToken, setUser } from '@/lib/api-client';

export default function Login() {
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try {
            const response = await api.postRaw<{ user: object; token: string }>('/auth/login', {
                username,
                password,
            });

            setToken(response.token);
            setUser(response.user);
            window.location.href = '/dashboard';
        } catch (err: unknown) {
            setError(err instanceof Error ? err.message : 'An unexpected error occurred.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <AuthLayout title="Log in to your account" description="Enter your credentials below to log in">
            <Head title="Log in" />

            <form className="flex flex-col gap-6" onSubmit={submit}>
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="username">Username</Label>
                        <Input
                            id="username"
                            type="text"
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="username"
                            value={username}
                            onChange={(e) => setUsername(e.target.value)}
                            disabled={loading}
                            placeholder="Username"
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            required
                            tabIndex={2}
                            autoComplete="current-password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            disabled={loading}
                            placeholder="Password"
                        />
                    </div>

                    {error && <InputError message={error} />}

                    <Button type="submit" className="mt-4 w-full" tabIndex={3} disabled={loading}>
                        {loading && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Log in
                    </Button>
                </div>

                <div className="text-muted-foreground text-center text-sm">
                    Don't have an account?{' '}
                    <TextLink href="/register" tabIndex={4}>
                        Sign up
                    </TextLink>
                </div>
            </form>
        </AuthLayout>
    );
}
