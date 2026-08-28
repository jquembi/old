{{--
    Componente: <x-map-editor municipio-id="..." tipo="..." :readonly="false" height="600" />

    Props:
        $municipioId  – ID do município (obrigatório)
        $tipo         – tipo de camada a editar (default: 'geral')
        $readonly     – se true, desactiva ferramentas de edição
        $height       – altura do editor em px (default: 600)
--}}
@props([
    'municipioId',
    'tipo'     => 'geral',
    'readonly' => false,
    'height'   => 600,
])

@once
    @push('styles')
        @vite('resources/css/editor.css')
        {{-- ol traz os seus próprios estilos internamente via JS --}}
    @endpush
@endonce

<div class="geo-editor-wrap" style="height: {{ (int) $height }}px;">

    {{-- Barra de ferramentas (escondida se readonly) --}}
    @unless($readonly)
    <div id="editor-toolbar" role="toolbar" aria-label="Ferramentas do editor de mapa">
        {{-- Botões injectados pelo Toolbar.js --}}
    </div>
    @endunless

    {{-- Corpo: painel de camadas + mapa --}}
    <div class="editor-body">

        {{-- Painel lateral de camadas --}}
        <aside id="layer-panel" aria-label="Camadas">
            <div class="panel-title">Camadas</div>
            <div id="layer-panel-list"></div>
        </aside>

        {{-- Mapa OpenLayers --}}
        <div id="map-editor" style="flex:1;position:relative;">

            {{-- Status: coordenadas do cursor --}}
            <div id="map-status">Lon — Lat —</div>

            {{-- Botão troca camada base --}}
            <button id="btn-toggle-base"
                    title="OpenStreetMap"
                    style="position:absolute;top:10px;right:50px;z-index:200;
                           background:#1a2236;border:1px solid #2a3448;border-radius:6px;
                           color:#9ca3af;padding:4px 8px;font-size:0.72rem;cursor:pointer;">
                OSM / Sat
            </button>

            {{-- Painel de Propriedades (posição absoluta sobre o mapa) --}}
            <div id="property-panel" class="hidden"></div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.MapEditor) return console.error('MapEditor n\u00e3o carregado');
                window.__mapEditor = new window.MapEditor({
                    container:   'map-editor',
                    municipioId: {{ (int) $municipioId }},
                    tipo:        '{{ $tipo }}',
                    readonly:    {{ $readonly ? 'true' : 'false' }},
