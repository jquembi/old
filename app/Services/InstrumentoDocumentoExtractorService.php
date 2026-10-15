<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class InstrumentoDocumentoExtractorService
{
    /**
     * Extrai dados de um ficheiro carregado ou existente no disco.
     */
    public function extrairDeFicheiro(string $caminhoFicheiro, ?string $nomeOriginal = null, ?string $tipoNome = null, ?string $municipioNome = null): array
    {
        if (! is_file($caminhoFicheiro) || ! is_readable($caminhoFicheiro)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Ficheiro não encontrado para leitura.',
                'texto' => '',
                'dados' => $this->dadosVazios(),
            ];
        }

        $extensao = strtolower(pathinfo($nomeOriginal ?: $caminhoFicheiro, PATHINFO_EXTENSION));
        $texto = '';
        $numPaginas = 1;
        $leitura = ['motor' => 'texto-nativo', 'avisos' => [], 'paginas_processadas' => 1];
        try {
            if (filesize($caminhoFicheiro) > 50 * 1024 * 1024) {
                throw new RuntimeException('O ficheiro excede o limite de 50 MB.');
            }
            if ($extensao === 'pdf' || in_array($extensao, ['png', 'jpg', 'jpeg', 'bmp', 'tif', 'tiff'])) {
                $leitura = app(NativeOcrService::class)->extract($caminhoFicheiro, $extensao === 'pdf' ? 'pdf' : 'image');
                $texto = $leitura['texto'] ?? '';
                $numPaginas = $leitura['paginas'] ?? 1;
            } elseif ($extensao === 'docx') {
                $texto = $this->extrairTextoDocx($caminhoFicheiro);
            } elseif (in_array($extensao, ['txt', 'csv'])) {
                $texto = file_get_contents($caminhoFicheiro) ?: '';
            } else {
                throw new RuntimeException('Leitura automática disponível para PDF, DOCX, TXT, CSV e imagens PNG, JPG, BMP ou TIFF.');
            }
        } catch (\Throwable $e) {
            return [
                'sucesso' => false, 'mensagem' => 'Não foi possível ler o documento: '.$e->getMessage(),
                'texto' => '', 'dados' => $this->dadosVazios(),
            ];
        }

        $texto = $this->normalizarTexto($texto);
        $dados = $this->analisarTexto($texto, $nomeOriginal ?: basename($caminhoFicheiro), $tipoNome, $municipioNome);
        $dados['num_paginas'] = $numPaginas;
        $dados['paginas_processadas'] = $leitura['paginas_processadas'];
        if ($texto === '') {
            $dados = $this->dadosVazios();
        }

        $temExtracao = trim($texto) !== '';

        return [
            'sucesso' => $temExtracao,
            'mensagem' => $temExtracao
                ? 'Leitura e análise concluídas.'.(! empty($leitura['avisos']) ? ' '.implode(' ', $leitura['avisos']) : '')
                : 'Não foi encontrado texto legível. Use uma imagem mais nítida ou com melhor iluminação.',
            'texto' => Str::limit($texto, 12000),
            'motor' => $leitura['motor'],
            'idioma' => $leitura['idioma'] ?? null,
            'avisos' => $leitura['avisos'] ?? [],
            'paginas_processadas' => $leitura['paginas_processadas'],
            'dados' => $dados,
        ];
    }

    /** Compatibilidade com os formulários de projectos SONA e Outros Projectos. */
    public function extrairMetadados(string $caminho, string $nomeOriginal, ?string $mimeType = null): array
    {
        $resultado = $this->extrairDeFicheiro($caminho, $nomeOriginal);
        if (! $resultado['sucesso']) {
            throw new RuntimeException($resultado['mensagem']);
        }

        return [
            'titulo_sugerido' => $resultado['dados']['titulo'],
            'resumo_extraido' => $resultado['dados']['descricao'],
            'estado_sugerido' => match (true) {
                (bool) preg_match('/\b(conclu[íi]do|finalizado|terminado)\b/iu', $resultado['texto']) => 'concluido',
                (bool) preg_match('/\b(suspenso|suspens[ãa]o)\b/iu', $resultado['texto']) => 'suspenso',
                (bool) preg_match('/\b(em curso|em execu[çc][ãa]o|em andamento)\b/iu', $resultado['texto']) => 'em_curso',
                (bool) preg_match('/\b(planificado|planejado)\b/iu', $resultado['texto']) => 'planificado',
                default => null,
            },
            'ano_detectado' => $resultado['dados']['ano_detectado'] ? $resultado['dados']['ano'] : null,
            'texto_extraido' => $resultado['texto'],
            'num_paginas' => $resultado['dados']['num_paginas'],
            'motor' => $resultado['motor'], 'avisos' => $resultado['avisos'],
        ];
    }

    private function extrairTextoDocx(string $caminho): string
    {
        $zip = new ZipArchive;
        if ($zip->open($caminho) !== true) {
            throw new RuntimeException('O documento DOCX está danificado ou não é um ficheiro Word válido.');
        }
        try {
            $parts = ['word/document.xml'];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (preg_match('#^word/(header|footer)[0-9]+\.xml$#', $name)) {
                    $parts[] = $name;
                }
            }
            $text = [];
            foreach ($parts as $part) {
                $info = $zip->statName($part);
                if (! $info || $info['size'] > 8 * 1024 * 1024) {
                    throw new RuntimeException('Conteúdo Word ausente ou demasiado grande para análise.');
                }
                $xml = $zip->getFromName($part);
                if (stripos($xml, '<!DOCTYPE') !== false || stripos($xml, '<!ENTITY') !== false) {
                    throw new RuntimeException('O documento contém declarações XML não suportadas.');
                }
                $document = new DOMDocument;
                $previous = libxml_use_internal_errors(true);
                try {
                    if (! $document->loadXML($xml, LIBXML_NONET)) {
                        throw new RuntimeException('O conteúdo XML do documento está danificado.');
                    }
                } finally {
                    libxml_clear_errors();
                    libxml_use_internal_errors($previous);
                }
                $xpath = new DOMXPath($document);
                $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                foreach ($xpath->query('//w:p') as $paragraph) {
                    $line = '';
                    foreach ($xpath->query('.//w:t | .//w:tab | .//w:br', $paragraph) as $node) {
                        $line .= match ($node->localName) {
                            'tab' => "\t", 'br' => "\n", default => $node->textContent,
                        };
                    }
                    if (trim($line) !== '') {
                        $text[] = trim($line);
                    }
                }
            }

            return implode("\n", $text);
        } finally {
            $zip->close();
        }
    }

    /**
     * Normaliza caracteres e quebras de linha para UTF-8 limpo.
     */
    private function normalizarTexto(string $texto): string
    {
        $texto = mb_convert_encoding($texto, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
        $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $texto);
        $texto = preg_replace('/[ \t]+/', ' ', $texto);
        $texto = preg_replace('/\n\s*\n+/', "\n\n", $texto);

        return trim($texto);
    }

    /**
     * Analisa o texto extraído para detectar Ano, Título, Estado, Descrição e Palavras-chave.
     */
    public function analisarTexto(string $texto, ?string $nomeFicheiro = null, ?string $tipoNome = null, ?string $municipioNome = null): array
    {
        $dados = $this->dadosVazios();

        // 1. Extração do Ano
        $anoExtraido = $this->extrairAno($texto, $nomeFicheiro);
        if ($anoExtraido) {
            $dados['ano'] = $anoExtraido;
            $dados['ano_detectado'] = true;
        } else {
            $dados['ano'] = (string) date('Y');
            $dados['ano_detectado'] = false;
        }

        // 2. Extração do Título
        $dados['titulo'] = $this->extrairTitulo($texto, $nomeFicheiro, $tipoNome, $municipioNome, $dados['ano']);

        // 3. Extração do Estado
        $dados['estado'] = $this->extrairEstado($texto);

        // 4. Extração da Descrição / Resumo Executivo
        $dados['descricao'] = $this->extrairDescricao($texto, $tipoNome, $municipioNome);

        // 5. Palavras-chave detectadas
        $dados['palavras_chave'] = $this->extrairPalavrasChave($texto);

        return $dados;
    }

    /**
     * Detecta o ano no texto ou no nome do ficheiro.
     */
    private function extrairAno(string $texto, ?string $nomeFicheiro = null): ?string
    {
        // 1. Padrões com contexto forte no texto (ex: "Ano: 2024", "Exercício de 2025", "Vigência 2024-2029", "Aprovado em 2023")
        $padroesContexto = [
            '/(?:vig[êe]ncia|aprova[çc][ãa]o|elabora[çc][ãa]o|exerc[íi]cio|ano\s*de\s*refer[êe]ncia|\bano\b|horizonte\s*temporal)\s*(?:de|em|para)?\s*[:\s-]*\b(20[123]\d)\b/iu',
            '/(?:decreto\s*(?:presidencial|executivo)?|resolu[çc][ãa]o|lei)\s*n[ºo°\. ]*\s*\d+\s*\/\s*(20[123]\d|\d{2})/iu',
            '/(?<!\d)(20[123]\d)\s*[-–—\/]\s*(?:20[123]\d|\d{2})(?!\d)/u', // Ex: 2024-2029 (pega 2024)
            '/(?:PMOT|PDM|PMDR|PLANO|INSTRUMENTO)\s*[-–—_ ]+\s*(?<!\d)(20[123]\d)(?!\d)/iu',
        ];

        foreach ($padroesContexto as $padrao) {
            if (preg_match($padrao, $texto, $matches)) {
                $ano = $matches[1];
                if (strlen($ano) === 2) {
                    $ano = '20'.$ano;
                }
                if ($this->anoValido((int) $ano)) {
                    return (string) $ano;
                }
            }
        }

        // 2. Se não encontrou no texto com contexto, procura no nome do ficheiro (ex: PMOT_Lubango_2025.pdf ou PMOT-2025.pdf)
        if ($nomeFicheiro && preg_match('/(?<!\d)(20[123]\d)(?!\d)/', $nomeFicheiro, $matches)) {
            if ($this->anoValido((int) $matches[1])) {
                return (string) $matches[1];
            }
        }

        // 3. Procura no texto por qualquer ano recente nos primeiros 1200 caracteres (área do cabeçalho)
        $cabecalho = mb_substr($texto, 0, 1200);
        if (preg_match_all('/(?<!\d)(20[123]\d)(?!\d)/', $cabecalho, $matches)) {
            $anos = array_filter($matches[1], fn ($a) => $this->anoValido((int) $a));
            if (! empty($anos)) {
                // Retorna o ano mais frequente ou o mais recente no cabeçalho
                $contagem = array_count_values($anos);
                arsort($contagem);

                return (string) key($contagem);
            }
        }

        return null;
    }

    /**
     * Valida se um ano é aceitável para um instrumento de ordenamento territorial.
     */
    private function anoValido(int $ano): bool
    {
        $anoAtual = (int) date('Y');

        return $ano >= 1990 && $ano <= ($anoAtual + 5);
    }

    /**
     * Extrai ou formata um título coerente para o instrumento.
     */
    private function extrairTitulo(string $texto, ?string $nomeFicheiro, ?string $tipoNome, ?string $municipioNome, ?string $ano): string
    {
        // Padrões de títulos explícitos nos primeiros 800 caracteres
        $cabecalho = mb_substr($texto, 0, 800);

        $padroesTitulo = [
            '/(?:Plano\s+Municipal\s+de\s+Ordenamento\s+do\s+Territ[óo]rio(?:[^\n\r]+)?)/iu',
            '/(?:Plano\s+Director\s+Municipal(?:[^\n\r]+)?)/iu',
            '/(?:Plano\s+de\s+Urbaniza[çc][ãa]o(?:[^\n\r]+)?)/iu',
            '/(?:Foral\s+Municipal(?:[^\n\r]+)?)/iu',
            '/(?:Carta\s+Municipal(?:[^\n\r]+)?)/iu',
            '/(?:Carta\s+Topogr[áa]fica(?:[^\n\r]+)?)/iu',
            '/(?:Carta\s+Tem[áa]tica(?:[^\n\r]+)?)/iu',
            '/(?:Carta\s+de\s+Ocupa[çc][ãa]o\s+do\s+Solo(?:[^\n\r]+)?)/iu',
            '/(?:PMOT\s*[-–—:][^\n\r]+)/iu',
        ];

        foreach ($padroesTitulo as $padrao) {
            if (preg_match($padrao, $cabecalho, $matches)) {
                $tituloEncontrado = trim($matches[0]);
                // Limitar tamanho
                if (mb_strlen($tituloEncontrado) >= 10 && mb_strlen($tituloEncontrado) <= 120) {
                    // Se estiver tudo em maiúsculas, formatar para Title Case limpo
                    if ($tituloEncontrado === mb_strtoupper($tituloEncontrado)) {
                        $tituloEncontrado = Str::title(mb_strtolower($tituloEncontrado));
                    }

                    return $tituloEncontrado;
                }
            }
        }

        // Se tiver tipo e município, gerar título institucional estruturado
        if ($tipoNome && $municipioNome) {
            $sufixoAno = $ano ? " - {$ano}" : '';

            return "{$tipoNome} de {$municipioNome}{$sufixoAno}";
        }

        // Fallback: nome do ficheiro sem extensão limpo
        if ($nomeFicheiro) {
            $base = pathinfo($nomeFicheiro, PATHINFO_FILENAME);
            $base = str_replace(['_', '-'], ' ', $base);
            $base = preg_replace('/\s+/', ' ', $base);

            return Str::title(trim($base));
        }

        return $tipoNome ?: 'Instrumento de Ordenamento do Território';
    }

    /**
     * Identifica o estado do instrumento com base no vocabulário oficial.
     */
    private function extrairEstado(string $texto): string
    {
        $amostra = mb_strtolower(mb_substr($texto, 0, 2000));

        // Aprovado
        if (
            str_contains($amostra, 'aprovado') ||
            str_contains($amostra, 'promulgado') ||
            str_contains($amostra, 'homologado') ||
            str_contains($amostra, 'em vigor') ||
            str_contains($amostra, 'diário da república') ||
            str_contains($amostra, 'decreto executivo')
        ) {
            return 'aprovado';
        }

        // Submetido
        if (
            str_contains($amostra, 'submetido') ||
            str_contains($amostra, 'consulta pública') ||
            str_contains($amostra, 'apreciação') ||
            str_contains($amostra, 'proposta técnica') ||
            str_contains($amostra, 'parecer técnico')
        ) {
            return 'submetido';
        }

        // Rejeitado
        if (
            str_contains($amostra, 'rejeitado') ||
            str_contains($amostra, 'desfavorável') ||
            str_contains($amostra, 'não aprovado')
        ) {
            return 'rejeitado';
        }

        return 'rascunho';
    }

    /**
     * Extrai uma descrição / sumário do plano.
     */
    private function extrairDescricao(string $texto, ?string $tipoNome = null, ?string $municipioNome = null): ?string
    {
        if (empty($texto)) {
            return $municipioNome && $tipoNome
                ? "{$tipoNome} elaborado no âmbito do planeamento e gestão territorial do Município de {$municipioNome}, Província da Huíla."
                : null;
        }

        // Procurar secções como "Resumo", "Objectivo", "Memória", "Enquadramento"
        $padroesResumo = [
            '/(?:resumo\s*executivo|resumo|objectivo\s*geral|enquadramento|âmbito\s*territorial)\s*[:\n\r]+([^\n\r]+(?:\n[^\n\r]+){1,4})/iu',
            '/(?:o\s+presente\s+plano[^\.\n\r]+\.[^\.\n\r]+\.)/iu',
            '/(?:esta\s+base\s+de\s+dados[^\.\n\r]+\.[^\.\n\r]+\.)/iu',
        ];

        foreach ($padroesResumo as $padrao) {
            if (preg_match($padrao, $texto, $matches)) {
                $resumo = trim(preg_replace('/\s+/', ' ', $matches[1] ?? $matches[0]));
                if (mb_strlen($resumo) > 30) {
                    return Str::limit($resumo, 400);
                }
            }
        }

        // Se não encontrar secção específica, pegar um trecho inicial limpo após cabeçalhos
        $linhas = array_filter(array_map('trim', explode("\n", $texto)));
        $linhasSignificativas = [];

        foreach ($linhas as $linha) {
            // Ignorar linhas curtas de cabeçalho administrativo comum
            if (mb_strlen($linha) < 25) {
                continue;
            }
            if (preg_match('/^(república|ministério|instituto|departamento|av\.|rotunda|tel:|email:)/iu', $linha)) {
                continue;
            }
            $linhasSignificativas[] = $linha;
            if (count($linhasSignificativas) >= 3) {
                break;
            }
        }

        if (! empty($linhasSignificativas)) {
            $descricao = implode(' ', $linhasSignificativas);

            return Str::limit(preg_replace('/\s+/', ' ', $descricao), 400);
        }

        return $municipioNome && $tipoNome
            ? "{$tipoNome} do Município de {$municipioNome}, Província da Huíla."
            : null;
    }

    /**
     * Extrai palavras-chave técnicas relevantes do plano.
     */
    private function extrairPalavrasChave(string $texto): array
    {
        $termos = [
            'Zoneamento', 'Ocupação do Solo', 'Reserva Fundiária', 'Área Urbana',
            'Área Rural', 'Expansão Urbana', 'Equipamentos Públicos', 'Rede Viária',
            'Infraestruturas', 'Património Natural', 'Cartografia', 'SIG', 'Topografia',
            'Escala', 'WGS84', 'UTM', 'Curvas de Nível', 'Hidrografia',
            'Diário da República', 'Consulta Pública', 'Homologação', 'INOTU',
        ];

        $encontrados = [];
        $amostra = mb_strtolower($texto);

        foreach ($termos as $termo) {
            if (str_contains($amostra, mb_strtolower($termo))) {
                $encontrados[] = $termo;
            }
        }

        return array_slice($encontrados, 0, 6);
    }

    /**
     * Retorna a estrutura vazia de dados padrão.
     */
    private function dadosVazios(): array
    {
        return [
            'ano' => (string) date('Y'),
            'ano_detectado' => false,
            'titulo' => '',
            'estado' => 'rascunho',
            'descricao' => '',
            'num_paginas' => 0,
            'palavras_chave' => [],
        ];
    }
}
