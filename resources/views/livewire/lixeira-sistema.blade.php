<div class="space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <flux:heading size="xl">Lixeira</flux:heading>
            <flux:text class="mt-1">Itens enviados para a lixeira e o utilizador que efectuou a eliminação.</flux:text>
        </div>

        <flux:select wire:model.live="tipo" class="w-56">
            <option value="todos">Todos os tipos</option>
            @foreach($tipos as $valor => $label)
                <option value="{{ $valor }}">{{ $label }}</option>
            @endforeach
        </flux:select>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Item</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Eliminado por</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Data</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500">Acções</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($itens as $item)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item['titulo'] }}</td>
                        <td class="px-4 py-3 text-sm text-zinc-500">{{ $item['rotulo'] }}</td>
                        <td class="px-4 py-3 text-sm text-zinc-500">{{ $item['deleted_by'] }}</td>
                        <td class="px-4 py-3 text-sm text-zinc-500">{{ $item['deleted_at']?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button wire:click="restaurar('{{ $item['tipo'] }}', {{ $item['id'] }})" size="sm" variant="ghost" icon="arrow-path">Restaurar</flux:button>
                                <flux:button
                                    x-on:click.prevent="$dispatch('confirmar', { title: 'Eliminar definitivamente', message: 'Esta acção não pode ser desfeita. Confirmar?', action: 'eliminarDefinitivo', component: $wire.$id, params: ['{{ $item['tipo'] }}', {{ $item['id'] }}], variant: 'danger', confirmLabel: 'Eliminar' })"
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    class="text-red-600 dark:text-red-400"
                                />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-sm text-zinc-500">A lixeira está vazia.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $itens->links() }}
</div>
