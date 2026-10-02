<?php

namespace App\Livewire;

use App\Models\Croqui;
use App\Models\CroquiProprietario;
use App\Models\Municipio;
use App\Models\OperacaoUrbanistica;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class TerrenoForm extends Component
{
    use WithFileUploads;

    public Municipio $municipio;
    public ?int $registoId = null;
    public ?Croqui $croqui = null;

    public string $titulo = '';
    public ?string $ano = null;
    public string $visibilidade = 'privado';
    public $ficheiro_pdf = null;
    public ?string $ficheiroPdfAtualCaminho = null;
    public ?string $ficheiroPdfAtualNome = null;
    public bool $rup_pago = false;
    public ?string $rup_referencia = null;

    public array $proprietarios = [
        ['nome' => '', 'bi' => '', 'contacto' => '']
    ];

    public string $utm_zone = '33';
    public string $utm_hemisphere = 'S';
    public array $coordenadas = [
        ['easting' => '', 'northing' => '']
    ];

    public function mount(Municipio $municipio, mixed $croqui = null): void
    {
        $this->municipio = $municipio;

        $user = Auth::user();
        if ($croqui) {
            $temPermissao = $user?->temAlgumaPermissao(['operacoes.editar', 'operacoes.editar_municipio', 'operacoes.editar_proprio']);
        } else {
            $temPermissao = $user?->temPermissao('operacoes.criar');
        }

        if (!$temPermissao) {
            abort(403, 'Não tem permissão para aceder ao formulário de terrenos ociosos.');
        }

        if ($croqui instanceof Croqui) {
            $this->croqui = $croqui;
            $this->carregarCroqui($croqui);
        } elseif ($croqui && is_numeric($croqui)) {
            $this->croqui = Croqui::where('tipo', 'terreno_ocioso')
                ->whereHas('operacao', fn($q) => $q->where('municipio_id', $municipio->id))
                ->findOrFail((int) $croqui);
            $this->carregarCroqui($this->croqui);
        } else {
            $this->ano = (string) now()->year;
        }
    }

    protected function carregarCroqui(Croqui $croqui): void
    {
        $this->registoId = $croqui->id;
        $this->titulo = $croqui->titulo ?? '';
        $this->ano = (string) ($croqui->ano ?? now()->year);
        $this->visibilidade = $croqui->visibilidade ?? 'privado';
        $this->ficheiroPdfAtualCaminho = $croqui->ficheiro_pdf_caminho;
        $this->ficheiroPdfAtualNome = $croqui->ficheiro_pdf_nome;
        $this->rup_pago = (bool) $croqui->rup_pago;
        $this->rup_referencia = $croqui->rup_referencia;
        $this->utm_zone = $croqui->utm_zone ?? '33';
        $this->utm_hemisphere = $croqui->utm_hemisphere ?? 'S';

        if ($croqui->proprietarios->isNotEmpty()) {
            $this->proprietarios = $croqui->proprietarios->map(fn($p) => [
                'nome' => $p->nome,
                'bi' => $p->bi ?? '',
                'contacto' => $p->contacto ?? '',
            ])->toArray();
        }

        if ($croqui->coordenadas_texto) {
            $coords = json_decode($croqui->coordenadas_texto, true);
            if (is_array($coords) && !empty($coords)) {
                $this->coordenadas = array_map(function($c) {
                    return [
                        'easting' => $c['easting'] ?? $c['lat_utm'] ?? '',
                        'northing' => $c['northing'] ?? $c['lon_utm'] ?? '',
                    ];
                }, $coords);
            }
        }
    }

    public function getUploadProgressProperty(): int
    {
        return $this->ficheiro_pdf ? 100 : 0;
    }

    public function adicionarProprietario(): void
    {
        $this->proprietarios[] = ['nome' => '', 'bi' => '', 'contacto' => ''];
    }

    public function removerProprietario(int $index): void
    {
        if (count($this->proprietarios) > 1) {
            unset($this->proprietarios[$index]);
            $this->proprietarios = array_values($this->proprietarios);
        }
    }

    public function adicionarCoordenada(): void
    {
        $this->coordenadas[] = ['easting' => '', 'northing' => ''];
    }

    public function removerCoordenada(int $index): void
    {
        if (count($this->coordenadas) > 1) {
            unset($this->coordenadas[$index]);
            $this->coordenadas = array_values($this->coordenadas);
        }
    }

    public function removerFicheiroPdfAtual(): void
    {
        $user = Auth::user();
        if (!$user || !$user->podeEnviarParaLixeira()) {
            abort(403, 'Não tem permissão para remover ficheiros.');
        }

        if ($this->croqui && $this->croqui->ficheiro_pdf_caminho) {
            Storage::disk('public')->delete($this->croqui->ficheiro_pdf_caminho);
            $this->croqui->update([
                'ficheiro_pdf_caminho' => null,
                'ficheiro_pdf_nome' => null,
            ]);
            $this->ficheiroPdfAtualCaminho = null;
            $this->ficheiroPdfAtualNome = null;
        }
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'ano' => ['nullable', 'string', 'max:4'],
            'visibilidade' => ['required', 'string', 'in:privado,publico'],
            'ficheiro_pdf' => ['nullable', 'file', 'mimes:pdf,docx', 'max:51200'],
            'rup_pago' => ['boolean'],
            'rup_referencia' => ['nullable', 'string', 'max:100'],
            'utm_zone' => ['required', 'string', 'in:32,33,34'],
            'utm_hemisphere' => ['required', 'string', 'in:S,N'],
            'proprietarios.*.nome' => ['required', 'string', 'max:255'],
            'proprietarios.*.bi' => ['nullable', 'string', 'max:50'],
            'proprietarios.*.contacto' => ['nullable', 'string', 'max:50'],
            'coordenadas.*.easting' => ['nullable', 'numeric'],
            'coordenadas.*.northing' => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'O título é obrigatório.',
            'proprietarios.*.nome.required' => 'O nome do proprietário é obrigatório.',
            'ficheiro_pdf.mimes' => 'O documento deve ser PDF ou Word (.docx).',
            'ficheiro_pdf.max' => 'O ficheiro não pode exceder 50MB.',
        ];
    }

    public function guardar()
    {
        $user = Auth::user();
        if ($this->registoId) {
            $temPermissao = $user?->temAlgumaPermissao(['operacoes.editar', 'operacoes.editar_municipio', 'operacoes.editar_proprio']);
        } else {
            $temPermissao = $user?->temPermissao('operacoes.criar');
        }

        if (!$temPermissao) {
            abort(403, 'Não tem permissão para guardar terrenos ociosos.');
        }

        $this->validate();

        $operacao = OperacaoUrbanistica::firstOrCreate(
            [
                'municipio_id' => $this->municipio->id,
                'titulo' => 'Terrenos Ociosos',
            ],
            [
                'descricao' => 'Operação urbanística de terrenos ociosos do município de ' . $this->municipio->nome,
                'criado_por' => Auth::id(),
            ]
        );

        $dados = [
            'operacao_id' => $operacao->id,
            'tipo' => 'terreno_ocioso',
            'titulo' => $this->titulo,
            'slug' => Str::slug($this->titulo) . '-' . Str::random(6),
            'ano' => $this->ano ?: (string) now()->year,
            'visibilidade' => $this->visibilidade,
            'rup_pago' => $this->rup_pago,
            'rup_referencia' => $this->rup_referencia,
            'utm_zone' => $this->utm_zone,
            'utm_hemisphere' => $this->utm_hemisphere,
            'coordenadas_texto' => json_encode($this->coordenadas),
        ];

        if ($this->rup_pago) {
            $dados['rup_pago_em'] = now();
        }

        if ($this->ficheiro_pdf) {
            $caminho = $this->ficheiro_pdf->store("terrenos_ociosos/{$this->municipio->id}", 'public');
            $dados['ficheiro_pdf_caminho'] = $caminho;
            $dados['ficheiro_pdf_nome'] = $this->ficheiro_pdf->getClientOriginalName();
        }

        if ($this->registoId) {
            $croqui = Croqui::where('tipo', 'terreno_ocioso')
                ->where('operacao_id', $operacao->id)
                ->findOrFail($this->registoId);
            $croqui->update($dados);

            $croqui->proprietarios()->delete();
            foreach ($this->proprietarios as $p) {
                if (!empty($p['nome'])) {
                    $croqui->proprietarios()->create([
                        'nome' => $p['nome'],
                        'bi' => $p['bi'] ?? null,
                        'contacto' => $p['contacto'] ?? null,
                    ]);
                }
            }

            session()->flash('status', "Terreno ocioso '{$croqui->titulo}' actualizado com sucesso.");
        } else {
            $dados['criado_por'] = Auth::id();
            $croqui = Croqui::create($dados);

            foreach ($this->proprietarios as $p) {
                if (!empty($p['nome'])) {
                    $croqui->proprietarios()->create([
                        'nome' => $p['nome'],
                        'bi' => $p['bi'] ?? null,
                        'contacto' => $p['contacto'] ?? null,
                    ]);
                }
            }

            session()->flash('status', "Terreno ocioso '{$croqui->titulo}' registado com sucesso.");
        }

        return redirect()->route('municipio.operacoes.terrenos-ociosos', $this->municipio);
    }

    public function render()
    {
        return view('livewire.terreno-form');
    }
}
