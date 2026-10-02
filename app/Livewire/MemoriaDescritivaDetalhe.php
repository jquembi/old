<?php

namespace App\Livewire;

use App\Models\Croqui;
use App\Models\Municipio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class MemoriaDescritivaDetalhe extends Component
{
    public Municipio $municipio;
    public Croqui $croqui;

    public function mount(Municipio $municipio, Croqui $croqui): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        if ($user->isMunicipal() && $user->municipio_id && $municipio->id !== $user->municipio_id) {
            abort(403, 'Não tem permissão para aceder a este documento.');
        }

        $this->municipio = $municipio;
        $this->croqui = $croqui;
    }

    public function render()
    {
        $conversorWordDisponivel = false;
        $qrUrl = $this->croqui->qr_token
            ? route('portal.consulta', ['codigo' => $this->croqui->codigo_territorial ?? $this->croqui->id])
            : url('/');

        $memoriaHtmlPreview = '';

        if ($this->croqui->memoria_descritiva_caminho && Storage::disk('public')->exists($this->croqui->memoria_descritiva_caminho)) {
            $conteudo = Storage::disk('public')->get($this->croqui->memoria_descritiva_caminho);
            $memoriaHtmlPreview = $this->gerarHtmlDeDocx($conteudo);
        }

        return view('livewire.memoria-descritiva-detalhe', [
            'conversorWordDisponivel' => $conversorWordDisponivel,
            'qrUrl' => $qrUrl,
            'memoriaHtmlPreview' => $memoriaHtmlPreview,
        ]);
    }

    protected function gerarHtmlDeDocx(string $conteudo): string
    {
        if (str_starts_with($conteudo, 'PK')) {
            $temp = tempnam(sys_get_temp_dir(), 'inotu_docx_');
            if ($temp === false) {
                return '';
            }

            file_put_contents($temp, $conteudo);

            try {
                $zip = new \ZipArchive();
                if ($zip->open($temp) === true) {
                    $xml = $zip->getFromName('word/document.xml');
                    $zip->close();
                    @unlink($temp);

                    if ($xml !== false) {
                        $html = '';
                        preg_match_all('/<w:p[^>]*>(.*?)<\/w:p>/s', $xml, $paragrafos);
                        if (!empty($paragrafos[1])) {
                            foreach ($paragrafos[1] as $p) {
                                preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/s', $p, $tMatches);
                                $linha = trim(implode('', $tMatches[1] ?? []));
                                if ($linha !== '') {
                                    $escaped = e($linha);
                                    if (preg_match('/^(MEMÓRIA|LIMITES|CONFRONTAÇÕES|ÁREA|COORDENADAS|LOCALIZAÇÃO|IDENTIFICAÇÃO)/i', $linha)) {
                                        $html .= "<h3>{$escaped}</h3>";
                                    } else {
                                        $html .= "<p>{$escaped}</p>";
                                    }
                                }
                            }
                        }
                        return $html;
                    }
                }
            } catch (\Throwable $e) {
                @unlink($temp);
            }
        }

        return '<p>' . nl2br(e(mb_substr($conteudo, 0, 5000))) . '</p>';
    }
}
