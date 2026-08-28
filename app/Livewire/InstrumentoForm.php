<?php

namespace App\Livewire;

use App\Models\Instrumento;
use App\Models\InstrumentoFicheiro;
use App\Models\Municipio;
use App\Models\TipoInstrumento;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Component;
use Livewire\WithFileUploads;

class InstrumentoForm extends Component
{
    use WithFileUploads;

    public Municipio $municipio;
    public TipoInstrumento $tipo;
    public string $tipoSlug;
    public ?int $registoId = null;
    public string $titulo = '';
    public ?string $ano = null;
    public string $estado = 'rascunho';
    public ?string $descricao = null;
    public array $ficheiros = [];
    public bool $isUploading = false;

    public function mount(Municipio $municipio, string $tipoSlug, mixed $instrumento = null): void
    {
        $this->municipio = $municipio;
        $this->tipoSlug = $tipoSlug;
        $this->tipo = TipoInstrumento::query()->with('categoria')->where('slug', $tipoSlug)->firstOrFail();
        $this->registoId = $instrumento instanceof Instrumento ? $instrumento->id : ($instrumento ? (int) $instrumento : null);

        if ($this->registoId) {
            $registo = $this->registoQuery()->findOrFail($this->registoId);
            $this->titulo = $registo->titulo;
            $this->ano = $registo->ano;
            $this->estado = $registo->estado;
            $this->descricao = $registo->descricao;
        }
    }

    public function getUploadProgressProperty(): int
    {
        return count($this->ficheiros) > 0 ? 100 : 0;
    }

    public function updatedFicheiros(): void
    {
        if ($this->titulo === '' && isset($this->ficheiros[0]) && $this->ficheiros[0] instanceof TemporaryUploadedFile) {
            $this->titulo = pathinfo($this->ficheiros[0]->getClientOriginalName(), PATHINFO_FILENAME);
        }

        if (! $this->ano) {
            $this->ano = now()->format('Y');
        }
    }

    public function guardar()
    {
        abort_unless(Auth::user()?->temPermissao($this->registoId ? 'instrumentos.editar_municipio' : 'instrumentos.criar'), 403);

        $this->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'ano' => ['nullable', 'digits:4'],
            'estado' => ['required', 'in:rascunho,submetido,aprovado,rejeitado'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'ficheiros.*' => ['file', 'max:51200'],
        ]);

        $registo = $this->registoId
            ? $this->registoQuery()->findOrFail($this->registoId)
            : new Instrumento([
                'municipio_id' => $this->municipio->id,
                'tipo_instrumento_id' => $this->tipo->id,
                'criado_por' => Auth::id(),
            ]);

        $registo->fill([
            'titulo' => $this->titulo,
            'ano' => $this->ano,
            'estado' => $this->estado,
            'descricao' => $this->descricao,
            'actualizado_por' => Auth::id(),
        ])->save();

        foreach ($this->ficheiros as $ficheiro) {
            if (! $ficheiro instanceof TemporaryUploadedFile) {
                continue;
            }

            $path = $ficheiro->store("instrumentos/{$registo->id}", 'public');

            InstrumentoFicheiro::create([
                'instrumento_id' => $registo->id,
                'nome_original' => $ficheiro->getClientOriginalName(),
                'caminho' => $path,
                'tipo_ficheiro' => strtolower($ficheiro->getClientOriginalExtension() ?: 'ficheiro'),
                'mime_type' => $ficheiro->getMimeType() ?: 'application/octet-stream',
                'tamanho' => $ficheiro->getSize() ?: 0,
                'enviado_por' => Auth::id(),
            ]);
        }

        return redirect()->route('municipio.instrumentos.tipo', [$this->municipio, $this->tipoSlug]);
    }

    public function render()
    {
        return view('livewire.instrumento-form', [
            'municipio' => $this->municipio,
            'tipo' => $this->tipo,
            'tipoSlug' => $this->tipoSlug,
            'registoId' => $this->registoId,
            'categoriaNome' => $this->tipo->categoria?->nome ?? 'Instrumentos',
            'categoriaRoute' => $this->categoriaRoute(),
            'isUploading' => $this->isUploading,
            'uploadProgress' => $this->uploadProgress,
            'ficheiros' => $this->ficheiros,
        ]);
    }

    private function registoQuery()
    {
        return Instrumento::query()
            ->where('municipio_id', $this->municipio->id)
            ->where('tipo_instrumento_id', $this->tipo->id);
    }

    private function categoriaRoute(): string
    {
        return match ($this->tipo->categoria?->slug) {
            'ordenamento-territorio' => route('municipio.instrumentos-ordenamento', $this->municipio),
            'instrumentos-urbanisticos' => route('municipio.instrumentos-urbanisticos', $this->municipio),
            'cartas-municipais' => route('municipio.cartas', $this->municipio),
            default => route('municipio.show', $this->municipio),
        };
    }
}
