<?php

namespace App\Livewire;

use App\Models\CategoriaInstrumento;
use App\Models\Instrumento;
use App\Models\InstrumentoFicheiro;
use App\Models\Municipio;
use App\Models\TipoInstrumento;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Component;
use Livewire\WithFileUploads;

class CartaMunicipalForm extends Component
{
    use WithFileUploads;

    public Municipio $municipio;
    public TipoInstrumento $tipo;
    public ?int $registoId = null;
    public string $titulo = '';
    public ?string $descricao = null;
    public ?string $ano = null;
    public string $estado = 'rascunho';
    public array $ficheiros = [];
    public bool $isUploading = false;

    public function mount(Municipio $municipio, mixed $instrumento = null): void
    {
        $this->municipio = $municipio;
        $this->tipo = $this->tipoCarta();
        $this->registoId = $instrumento instanceof Instrumento ? $instrumento->id : ($instrumento ? (int) $instrumento : null);

        if ($this->registoId) {
            $registo = $this->cartaQuery()->findOrFail($this->registoId);
            $this->titulo = $registo->titulo;
            $this->descricao = $registo->descricao;
            $this->ano = $registo->ano;
            $this->estado = $registo->estado;
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
        abort_unless(Auth::user()?->temPermissao($this->registoId ? 'cartas_municipais.editar' : 'cartas_municipais.criar'), 403);

        $this->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'ano' => ['nullable', 'digits:4'],
            'estado' => ['required', 'in:rascunho,submetido,aprovado,rejeitado'],
            'ficheiros.*' => ['file', 'max:51200'],
        ]);

        $registo = $this->registoId
            ? $this->cartaQuery()->findOrFail($this->registoId)
            : new Instrumento([
                'municipio_id' => $this->municipio->id,
                'tipo_instrumento_id' => $this->tipo->id,
                'criado_por' => Auth::id(),
            ]);

        $registo->fill([
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'ano' => $this->ano,
            'estado' => $this->estado,
            'actualizado_por' => Auth::id(),
        ])->save();

        foreach ($this->ficheiros as $ficheiro) {
            if (! $ficheiro instanceof TemporaryUploadedFile) {
                continue;
            }

            $path = $ficheiro->store("cartas-municipais/{$registo->id}", 'public');

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

        return redirect()->route('municipio.cartas', $this->municipio);
    }

    public function render()
    {
        return view('livewire.carta-municipal-form', [
            'municipio' => $this->municipio,
            'registoId' => $this->registoId,
            'isUploading' => $this->isUploading,
            'uploadProgress' => $this->uploadProgress,
            'ficheiros' => $this->ficheiros,
        ]);
    }

    private function cartaQuery()
    {
        return Instrumento::query()
            ->where('municipio_id', $this->municipio->id)
            ->where('tipo_instrumento_id', $this->tipo->id);
    }

    private function tipoCarta(): TipoInstrumento
    {
        $categoria = CategoriaInstrumento::query()
            ->where('slug', 'cartas-municipais')
            ->firstOrFail();

        return TipoInstrumento::query()
            ->where('categoria_id', $categoria->id)
            ->orderBy('ordem')
            ->firstOrFail();
    }
}
