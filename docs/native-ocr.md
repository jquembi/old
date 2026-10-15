# OCR nativo

O serviço usa Windows.Media.Ocr e Windows.Data.Pdf do Windows 10/11, sem Tesseract, serviços online, downloads ou novos pacotes. DOCX é lido com as extensões ZIP e DOM do PHP; TXT/CSV são lidos diretamente. O motor OCR do Windows depende dos idiomas já instalados no sistema; nesta instalação existem pt-PT e pt-BR. Em outros sistemas, a interface apresenta a indisponibilidade do motor, sem devolver sucesso fictício.

## Leitura

- PNG, JPG/JPEG, BMP e TIFF; TIFF com várias páginas.
- PDF digital ou digitalizado, renderizado nativamente até cerca de 300 DPI, com limite de 16 milhões de pixels por página.
- Ampliação de imagens pequenas, orientações 0/90/180/270 graus, segunda passagem com limiar de Otsu para imagens inteiras na orientação original.
- Recortes sobrepostos para mapas grandes; o limite de recortes produz aviso explícito. O resultado escolhido usa uma heurística de texto legível, não uma percentagem de confiança.
- Texto organizado por linhas e páginas; as sugestões continuam editáveis. Fotografias desfocadas, manuscritos e layouts complexos podem exigir correção manual.
- DOCX inclui parágrafos, texto em tabelas, cabeçalhos e rodapés, descodificação de entidades XML e rejeição de declarações externas. DOC binário, DWG, ZIP de mapas e outros formatos não são tratados como texto OCR.

## Limites

Ficheiros até 50 MB. Configuração em config/ocr.php: OCR_LANGUAGE (pt-PT), OCR_MAX_PAGES (20, máximo 100), OCR_MAX_TILES (16, máximo 32), OCR_TIMEOUT (120 segundos, máximo 600). A leitura parcial informa quantas páginas foram processadas e mantém o total do documento. O timeout devolve uma falha explícita; os limites de execução do servidor PHP precisam permitir o tempo configurado.

O script scripts/ocr_image.ps1 aceita -ImagePath, -Format image/pdf/auto, -Language, -MaxPages, -MaxTiles e -Json. O PHP chama o processo por argumentos separados; nenhum nome de ficheiro é interpretado como comando. Streams, bitmaps e páginas são libertados; não são criadas imagens temporárias do documento.

## Verificação

php artisan test --compact tests/Unit/NativeOcrServiceTest.php tests/Unit/InstrumentoDocumentoExtractorServiceTest.php

As imagens em tests/Fixtures/ocr foram geradas localmente com texto sintético, sem dados pessoais. Os testes nativos usam o motor real; em sistemas sem Windows são ignorados. Os restantes testes cobrem parsing DOCX, erros, texto vazio e o contrato de metadados.
