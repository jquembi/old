<x-layouts::app :title="$title">
    <div class="mx-auto w-full max-w-5xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <header class="border-b-2 border-angola-red pb-5">
            <p class="text-sm font-semibold text-angola-red">Núcleo Nacional</p>
            <h1 class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-50">{{ $title }}</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $description }}</p>
        </header>

        @if(session('info'))
            <div class="mt-6 border-s-4 border-angola-red bg-zinc-50 px-4 py-3 text-sm text-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                {{ session('info') }}
            </div>
        @endif

        <section class="card mt-6 p-6">
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-50">Módulo disponível</h2>
            <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                Esta área está pronta para receber os dados e as operações específicas deste módulo, sem interromper a navegação da plataforma.
            </p>
        </section>
    </div>
</x-layouts::app>
