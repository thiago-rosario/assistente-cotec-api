<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Status do Container</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-50">
    <main class="mx-auto flex min-h-screen max-w-3xl items-center px-6 py-16">
        <section class="w-full rounded-3xl border border-emerald-500/30 bg-slate-900/80 p-8 shadow-2xl shadow-emerald-950/40">
            <p class="mb-4 inline-flex rounded-full border border-emerald-400/30 bg-emerald-500/10 px-3 py-1 text-sm text-emerald-300">
                Container ativo
            </p>
            <h1 class="text-3xl font-semibold tracking-tight">OK, o container esta rodando.</h1>
            <p class="mt-4 text-base text-slate-300">
                A aplicacao esta disponivel em
                <a class="text-emerald-300 underline decoration-emerald-500/50 underline-offset-4" href="{{ config('app.url') }}">
                    {{ config('app.url') }}
                </a>.
            </p>
        </section>
    </main>
</body>
</html>
