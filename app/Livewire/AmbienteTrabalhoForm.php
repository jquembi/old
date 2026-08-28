<?php

namespace App\Livewire;

use App\Models\ArquivoBase;
use App\Models\Municipio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class AmbienteTrabalhoForm extends Component
{
    use WithFileUploads;

    public Municipio $municipio;
    public string $uploadTitulo = '';
    public string $uploadTipo = 'qgis';
    public ?TemporaryUploadedFile $uploadFicheiro = null;
    public bool $isUploading = false;

    public function mount(Municipio $municipio): void
    {
        $this->municipio = $municipio;
    }

    public function getUploadProgressProperty(): int
    {
        return $this->uploadFicheiro ? 100 : 0;
    }

    public function enviar()
    {
        $this->validate([
            'uploadTitulo' => ['required', 'string', 'max:255'],
            'uploadTipo' => ['required', 'in:qgis,dwg,shp,geojson,outro'],
            'uploadFicheiro' => ['required', 'file', 'max:204800'],
        ]);

        $ficheiro = $this->uploadFicheiro;

        if (! $ficheiro) {
            return;
        }

        $path = $ficheiro->store("ambiente-trabalho/{$this->municipio->id}", 'public');

        ArquivoBase::create([
            'municipio_id' => $this->municipio->id,
            'tipo' => $this->uploadTipo,
            'titulo' => $this->uploadTitulo,
            'slug' => $this->uniqueSlug($this->uploadTitulo),
            'ficheiro_caminho' => $path,
            'ficheiro_nome' => $ficheiro->getClientOriginalName(),
            'enviado_por' => Auth::id(),
        ]);

        return redirect()->route('municipio.operacoes.ambiente-trabalho', $this->municipio);
    }

    public function render()
    {
        return view('livewire.ambiente-trabalho-form', [
            'municipio' => $this->municipio,
            'isUploading' => $this->isUploading,
            'uploadProgress' => $this->uploadProgress,
            'uploadFicheiro' => $this->uploadFicheiro,
        ]);
    }

    private function uniqueSlug(string $titulo): string
    {
        $base = Str::slug($titulo) ?: 'arquivo';
        $slug = $base;
        $counter = 1;

        while (ArquivoBase::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
