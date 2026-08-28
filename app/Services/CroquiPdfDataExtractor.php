<?php

namespace App\Services;

use Illuminate\Support\Str;

class CroquiPdfDataExtractor
{
    public function extrairDeConteudo(string $conteudo, string $nomeFicheiro): array
    {
        return $this->extrairMemoriaDescritivaDeConteudo($conteudo, $nomeFicheiro);
    }

    public function extrairMemoriaDescritivaDeConteudo(string $conteudo, string $nomeFicheiro): array
    {
        $dados = [
            'titulo' => '',
            'area' => '',
            'perimetro' => '',
            'dimensao' => '',
            'escala' => '',
            'tecnico_responsavel' => '',
            'folha_numero' => '',
            'data_folha' => '',
            'ano' => '',
            'proprietarios' => [['nome' => '', 'bi' => '', 'contacto' => '']],
            'coordenadas' => [],
        ];

        $texto = $this->extrairTextoDeDocx($conteudo);
        
        if (empty($texto)) {
            return [
                'preenchido' => false,
                'mensagem' => 'Não foi possível extrair texto do documento.',
                'dados' => $dados,
            ];
        }

        // Normalizar texto para UTF-8 válido
        $texto = $this->normalizarTexto($texto);

        // Extrair proprietário
        $proprietario = $this->extrairProprietario($texto);
        $dados['proprietarios'][0] = $proprietario;

        // Extrair título
        $dados['titulo'] = $this->extrairTitulo($texto, $proprietario['nome']);

        // Extrair área
        $dados['area'] = $this->extrairArea($texto);

        // Extrair perímetro
        $dados['perimetro'] = $this->extrairPerimetro($texto);

        // Extrair dimensão
        $dados['dimensao'] = $this->extrairDimensao($texto);

        // Extrair escala
        $dados['escala'] = $this->extrairEscala($texto);

        // Extrair técnico responsável
        $dados['tecnico_responsavel'] = $this->extrairTecnicoResponsavel($texto);

        // Extrair data folha
        $dados['data_folha'] = $this->extrairDataFolha($texto);

        // Extrair ano
        $dados['ano'] = $this->extrairAno($texto);

        // Extrair coordenadas
        $dados['coordenadas'] = $this->extrairCoordenadas($texto);

        $temDados = !empty($dados['area']) || !empty($dados['perimetro']) || 
                    !empty($dados['proprietarios'][0]['nome']) || 
                    !empty($dados['coordenadas']);

        return [
            'preenchido' => $temDados,
            'mensagem' => $temDados ? 'Dados extraídos com sucesso do documento.' : 'Não foi possível extrair dados do documento.',
            'dados' => $dados,
        ];
    }

    private function extrairTextoDeDocx(string $conteudo): string
    {
        // Tentar extrair texto de DOCX (formato ZIP)
        if (strpos($conteudo, 'PK') === 0) {
            $temp = tempnam(sys_get_temp_dir(), 'inotu_extract_');
            
            if ($temp === false) {
                return '';
            }
            
            if (file_put_contents($temp, $conteudo) === false) {
                @unlink($temp);
                return '';
            }
            
            try {
                $zip = new \ZipArchive();
                $status = $zip->open($temp);
                
                if ($status === true) {
                    $xml = $zip->getFromName('word/document.xml');
                    if ($xml !== false) {
                        $zip->close();
                        @unlink($temp);
                        
                        // Extrair texto do XML do Word com quebras de linha
                        $texto = '';
                        
                        // Abordagem melhorada: processar parágrafos individualmente
                        preg_match_all('/<w:p[^>]*>(.*?)<\/w:p>/s', $xml, $paragrafos);
                        
                        if (!empty($paragrafos[1])) {
                            foreach ($paragrafos[1] as $paragrafo) {
                                // Extrair texto dentro do parágrafo
                                $textoParagrafo = '';
                                preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/s', $paragrafo, $textMatches);
                                if (!empty($textMatches[1])) {
                                    $textoParagrafo = implode('', $textMatches[1]);
                                }
                                // Adicionar quebra de linha se houver conteúdo
                                if (!empty(trim($textoParagrafo))) {
                                    $texto .= trim($textoParagrafo) . "\n";
                                }
                            }
                        }
                        
                        // Limpar espaços extras
                        $texto = preg_replace('/[ \t]+/', ' ', $texto);
                        $texto = trim($texto);
                        
                        return $texto;
                    }
                    $zip->close();
                }
            } catch (\Exception $e) {
                // Silenciar erro e tentar outro método
                @unlink($temp);
                return '';
            }
            @unlink($temp);
        }

        // Tentar extrair de PDF (texto simples)
        if (strpos($conteudo, '%PDF') === 0) {
            // Para PDF, seria necessário uma biblioteca específica
            // Por agora, retornar vazio
            return '';
        }

        // Se não for DOCX nem PDF, tentar extrair como texto simples
        return strip_tags($conteudo);
    }

    private function normalizarTexto(string $texto): string
    {
        // Converter para UTF-8 e remover caracteres inválidos
        $texto = mb_convert_encoding($texto, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
        $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $texto);
        // Substituir caracteres mal codificados
        $texto = mb_convert_encoding($texto, 'UTF-8', 'UTF-8');
        return $texto;
    }

    private function extrairProprietario(string $texto): array
    {
        $nome = '';
        $bi = '';
        $contacto = '';

        // Padrões comuns para nome
        $padroesNome = [
            '/(?:Nome|Requerente|Proprietário)\s*[:]\s*([^\n\r]+)/i',
            '/(?:Nome|Name)\s*[\/]\s*[^\n\r]*\n([^\n\r]+)/i',
        ];

        foreach ($padroesNome as $padrao) {
            if (preg_match($padrao, $texto, $matches)) {
                $nome = trim($matches[1]);
                break;
            }
        }

        // Padrão para BI/NIF
        if (preg_match('/(?:BI\s*\/?\s*NIF|Bilhete\s*de\s*Identidade)\s*n\.?\s*º\s*:?\s*([A-Z0-9]+)/i', $texto, $matches)) {
            $bi = trim($matches[1]);
        }

        return [
            'nome' => $nome,
            'bi' => $bi,
            'contacto' => $contacto,
        ];
    }

    private function extrairTitulo(string $texto, string $nomeProprietario): string
    {
        if (!empty($nomeProprietario)) {
            return "Croqui de Localização de {$nomeProprietario}";
        }
        return '';
    }

    private function extrairArea(string $texto): string
    {
        $padroes = [
            '/(?:Área\s*Total|Area)\s*[:]\s*([\d.,]+)\s*m[²2]/i',
            '/(?:Área|Area)\s*[:]\s*([\d.,]+)\s*m[²2]/i',
        ];

        foreach ($padroes as $padrao) {
            if (preg_match($padrao, $texto, $matches)) {
                return str_replace(',', '.', trim($matches[1]));
            }
        }

        return '';
    }

    private function extrairPerimetro(string $texto): string
    {
        $padroes = [
            '/(?:Perímetro|Perimetro)\s*[:]\s*([\d.,]+)\s*m/i',
        ];

        foreach ($padroes as $padrao) {
            if (preg_match($padrao, $texto, $matches)) {
                return str_replace(',', '.', trim($matches[1]));
            }
        }

        return '';
    }

    private function extrairDimensao(string $texto): string
    {
        $padroes = [
            '/(?:Dimensão|Dimensao)\s*[:]\s*([^\n\r]+)/i',
            '/(\d+[,.]?\d*)\s*m\s*[x×]\s*(\d+[,.]?\d*)\s*m/i',
        ];

        foreach ($padroes as $padrao) {
            if (preg_match($padrao, $texto, $matches)) {
                if (isset($matches[1]) && isset($matches[2])) {
                    return str_replace(',', '.', trim($matches[1])) . ' m x ' . str_replace(',', '.', trim($matches[2])) . ' m';
                }
                return trim($matches[1]);
            }
        }

        return '';
    }

    private function extrairEscala(string $texto): string
    {
        $padroes = [
            '/Escala\s*[:]\s*(1\/\d+)/i',
            '/1\/\d+/',
        ];

        foreach ($padroes as $padrao) {
            if (preg_match($padrao, $texto, $matches)) {
                // Se o padrão capturar apenas o valor (grupo 1), usar isso
                // Se capturar tudo, extrair o valor 1/XXX
                if (isset($matches[1])) {
                    return trim($matches[1]);
                }
                // Extrair apenas o padrão 1/XXX do match completo
                if (preg_match('/(1\/\d+)/', $matches[0], $valor)) {
                    return $valor[1];
                }
                return trim($matches[0]);
            }
        }

        return '';
    }

    private function extrairTecnicoResponsavel(string $texto): string
    {
        $padroes = [
            '/(?:Técnico\s*Responsável|Tecnico\s*Responsavel)\s*[:]\s*([^\n\r]+)/i',
        ];

        foreach ($padroes as $padrao) {
            if (preg_match($padrao, $texto, $matches)) {
                return trim($matches[1]);
            }
        }

        return '';
    }

    private function extrairDataFolha(string $texto): string
    {
        $padroes = [
            '/(?:Data|Data\s*Folha|Mês\/Ano)\s*[:]\s*(\d{2}\/\d{4})/i',
            '/(\d{2}\/\d{4})/',
        ];

        foreach ($padroes as $padrao) {
            if (preg_match($padrao, $texto, $matches)) {
                return trim($matches[1]);
            }
        }

        return '';
    }

    private function extrairAno(string $texto): string
    {
        $padroes = [
            '/Ano\s*[:]\s*(\d{4})/i',
            '/Data\s*[:]\s*\d{2}\/(\d{4})/i',
            '/\/(\d{4})/',
        ];

        foreach ($padroes as $padrao) {
            if (preg_match($padrao, $texto, $matches)) {
                return trim($matches[1]);
            }
        }

        return '';
    }

    private function extrairCoordenadas(string $texto): array
    {
        $coordenadas = [];
        
        // Padrão para coordenadas GMS e UTM
        // Exemplo: 15°01'37.51"S 8338077.24 13°25'41.48"E 331021.56
        $padrao = '/(\d{1,2})°(\d{1,2})\'([\d.]+)"([NS])\s*(\d+\.?\d*)\s*(\d{1,2})°(\d{1,2})\'([\d.]+)"([EW])\s*(\d+\.?\d*)/';
        
        preg_match_all($padrao, $texto, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $latGms = sprintf("%02d°%02d'%s\"%s", $match[1], $match[2], $match[3], $match[4]);
            $latUtm = $match[5];
            $lonGms = sprintf("%02d°%02d'%s\"%s", $match[6], $match[7], $match[8], $match[9]);
            $lonUtm = $match[10];
            
            $coordenadas[] = [
                'lat_gms' => $latGms,
                'lat_utm' => $latUtm,
                'lon_gms' => $lonGms,
                'lon_utm' => $lonUtm,
            ];
        }
        
        // Se não encontrou coordenadas, retornar array vazio
        if (empty($coordenadas)) {
            return [['lat_gms' => '', 'lat_utm' => '', 'lon_gms' => '', 'lon_utm' => '']];
        }
        
        return $coordenadas;
    }
}
