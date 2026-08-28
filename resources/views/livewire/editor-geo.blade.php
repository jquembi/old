<div class="editor-qgis-root">
    {{-- ═══ EDITOR GIS — Layout QGIS-like ═══ --}}
    <div class="editor-qgis">

        {{-- ── Barra de título ── --}}
        <div class="qgis-titlebar">
            <div class="qgis-titlebar__left">
                <a href="{{ route('municipio.show', $municipio) }}" class="qgis-back" wire:navigate title="Voltar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                </a>
                <span class="qgis-project-name">INOTU — {{ $municipio->nome }}</span>
                <span class="qgis-badge">Editor Geoespacial</span>
            </div>
            <div class="qgis-titlebar__right">
                <span class="qgis-feat-count"><span id="feature-count">0</span> features</span>
                <span id="save-status" class="save-status">Pronto</span>
            </div>
        </div>

        {{-- ── Barra de ferramentas ── --}}
        @unless($readonly)
        <div id="editor-toolbar" role="toolbar" aria-label="Ferramentas"></div>
        @endunless

        {{-- ── Área de trabalho: camadas + mapa ── --}}
        <div class="qgis-workspace">
            {{-- Painel de camadas --}}
            <aside id="layer-panel" aria-label="Camadas">
                <div class="panel-header">
                    <span class="panel-title">Camadas</span>
                </div>
                <div id="layer-panel-list" style="flex:1;overflow-y:auto;padding:4px 0;"></div>
            </aside>

            {{-- Canvas do mapa --}}
            <div id="map-editor" class="qgis-canvas">
                <button id="btn-toggle-base" class="qgis-base-toggle" title="Alternar base">OSM / Sat</button>
                <div id="property-panel" class="hidden"></div>
                <div id="sync-toast" class="hidden"></div>
            </div>
        </div>

        {{-- ── Barra de estado (QGIS-like) ── --}}
        <div id="editor-statusbar" class="qgis-statusbar">
            <div class="statusbar-section">
                <span id="status-coords" class="statusbar-item statusbar-coords">Lon — &nbsp; Lat —</span>
            </div>
            <div class="statusbar-sep"></div>
            <div class="statusbar-section">
                <span class="statusbar-item statusbar-crs">EPSG:4326</span>
            </div>
            <div class="statusbar-sep"></div>
            <div class="statusbar-section">
                <span id="status-scale" class="statusbar-item">1:—</span>
            </div>
            <div class="statusbar-sep"></div>
            <div class="statusbar-section">
                <span id="status-zoom" class="statusbar-item">Z —</span>
            </div>
            <div class="statusbar-sep"></div>
            <div class="statusbar-section statusbar-section--right">
                <span id="status-municipio" class="statusbar-item statusbar-muni">{{ $municipio->nome }} · {{ $municipio->provincia->nome ?? 'Angola' }}</span>
            </div>
        </div>
    </div>

    @script
    <script>
        window.MapEditor
            ? (() => {
                window.__mapEditor = new window.MapEditor({
                    container:     'map-editor',
                    municipioId:   {{ $municipio->id }},
                    tipo:          '{{ $tipo }}',
                    readonly:      {{ $readonly ? 'true' : 'false' }},
                    municipioNome: '{{ addslashes($municipio->nome) }}',
                    provinciaNome: '{{ addslashes($municipio->provincia->nome ?? "Angola") }}',
                });
            })()
            : console.error('MapEditor não carregado. Verifique se o app.js está incluído.');
    </script>
    @endscript
</div>
