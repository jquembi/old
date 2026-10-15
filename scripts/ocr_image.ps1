param(
    [Parameter(Mandatory=$true)][string]$ImagePath,
    [ValidateSet('auto','image','pdf')][string]$Format = 'auto',
    [ValidateRange(1,100)][int]$MaxPages = 20,
    [ValidateRange(1,32)][int]$MaxTiles = 16,
    [string]$Language = 'pt-PT',
    [switch]$Json
)
$ErrorActionPreference = 'Stop'
[Console]::OutputEncoding = New-Object System.Text.UTF8Encoding($false)
$ProgressPreference = 'SilentlyContinue'
try {
    Add-Type -AssemblyName System.Runtime.WindowsRuntime
    $null = [Windows.Storage.StorageFile,Windows.Storage,ContentType=WindowsRuntime]
    $null = [Windows.Graphics.Imaging.BitmapDecoder,Windows.Graphics.Imaging,ContentType=WindowsRuntime]
    $null = [Windows.Graphics.Imaging.SoftwareBitmap,Windows.Graphics.Imaging,ContentType=WindowsRuntime]
    $null = [Windows.Media.Ocr.OcrEngine,Windows.Foundation,ContentType=WindowsRuntime]
    $null = [Windows.Globalization.Language,Windows.Globalization,ContentType=WindowsRuntime]
    $null = [Windows.Data.Pdf.PdfDocument,Windows.Data.Pdf,ContentType=WindowsRuntime]
    $null = [Windows.Storage.Streams.InMemoryRandomAccessStream,Windows.Storage.Streams,ContentType=WindowsRuntime]
    $null = [Windows.Security.Cryptography.CryptographicBuffer,Windows.Security.Cryptography,ContentType=WindowsRuntime]
    $script:asyncMethod = [System.WindowsRuntimeSystemExtensions].GetMethods() | Where-Object {
        $_.Name -eq 'AsTask' -and $_.IsGenericMethod -and $_.GetGenericArguments().Count -eq 1 -and $_.GetParameters().Count -eq 1
    } | Select-Object -First 1
    $script:actionMethod = [System.WindowsRuntimeSystemExtensions].GetMethods() | Where-Object {
        $_.Name -eq 'AsTask' -and -not $_.IsGenericMethod -and $_.GetParameters().Count -eq 1 -and $_.GetParameters()[0].ParameterType.Name -eq 'IAsyncAction'
    } | Select-Object -First 1
    function Await($operation, [Type]$type) {
        $task = $script:asyncMethod.MakeGenericMethod($type).Invoke($null, @($operation))
        $task.GetAwaiter().GetResult()
    }
    function AwaitAction($operation) {
        $task = $script:actionMethod.Invoke($null, @($operation))
        $null = $task.GetAwaiter().GetResult()
    }
    # Native byte processing: normalize contrast and binarize with Otsu's threshold.
    Add-Type -TypeDefinition @"
using System;
public static class OcrPixels {
    public static byte[] Enhance(byte[] pixels) {
        int[] histogram = new int[256];
        for (int i=0; i<pixels.Length; i+=4) {
            int alpha = pixels[i+3];
            int gray = (pixels[i]*114 + pixels[i+1]*587 + pixels[i+2]*299)/1000;
            gray = (gray*alpha + 255*(255-alpha))/255;
            histogram[gray]++;
            pixels[i]=pixels[i+1]=pixels[i+2]=(byte)gray; pixels[i+3]=255;
        }
        long total=pixels.Length/4, sum=0, backgroundSum=0, background=0;
        for(int i=0;i<256;i++) sum+=(long)i*histogram[i];
        double maximum=-1; int threshold=127;
        for(int i=0;i<256;i++) {
            background+=histogram[i]; if(background==0) continue;
            long foreground=total-background; if(foreground==0) break;
            backgroundSum+=(long)i*histogram[i];
            double delta=(double)backgroundSum/background-(double)(sum-backgroundSum)/foreground;
            double variance=(double)background*foreground*delta*delta;
            if(variance>maximum){maximum=variance;threshold=i;}
        }
        for(int i=0;i<pixels.Length;i+=4) {
            byte value=(byte)(pixels[i]>threshold ? 255 : 0);
            pixels[i]=pixels[i+1]=pixels[i+2]=value;
        }
        return pixels;
    }
}
"@
    $script:warnings = New-Object 'System.Collections.Generic.List[string]'
    $languages = @([Windows.Media.Ocr.OcrEngine]::AvailableRecognizerLanguages)
    $selected = $languages | Where-Object LanguageTag -eq $Language | Select-Object -First 1
    if (-not $selected) { $selected = $languages | Where-Object LanguageTag -like 'pt-*' | Select-Object -First 1 }
    if (-not $selected) {
        $selected = $languages | Select-Object -First 1
        $script:warnings.Add('Idioma portugues indisponivel; foi usado outro idioma instalado no Windows.')
    }
    if (-not $selected) { throw 'O Windows nao possui um idioma de reconhecimento OCR instalado.' }
    $script:engine = [Windows.Media.Ocr.OcrEngine]::TryCreateFromLanguage($selected)
    if (-not $script:engine) { throw 'Nao foi possivel iniciar o OCR nativo do Windows.' }
    function Score([string]$text) {
        $letters = [regex]::Matches($text, '[\p{L}\p{N}]').Count
        $words = [regex]::Matches($text, '\b[\p{L}]{3,}\b').Count
        $known = [regex]::Matches($text, '(?i)\b(plano|municipal|municipio|republica|angola|provincia|territorio|ano|projecto|projeto|governo|area|de|do|da|para|com|em)\b').Count
        return $letters + $words*3 + $known*20
    }
    function Recognize($decoder, $transform, [switch]$Enhanced) {
        $bitmap = $null
        try {
            if ($Enhanced) {
                $data = Await ($decoder.GetPixelDataAsync([Windows.Graphics.Imaging.BitmapPixelFormat]::Bgra8, [Windows.Graphics.Imaging.BitmapAlphaMode]::Straight, $transform, [Windows.Graphics.Imaging.ExifOrientationMode]::RespectExifOrientation, [Windows.Graphics.Imaging.ColorManagementMode]::DoNotColorManage)) ([Windows.Graphics.Imaging.PixelDataProvider])
                $pixels = [OcrPixels]::Enhance($data.DetachPixelData())
                $buffer = [System.Runtime.InteropServices.WindowsRuntime.WindowsRuntimeBufferExtensions]::AsBuffer([byte[]]$pixels)
                $bitmap = [Windows.Graphics.Imaging.SoftwareBitmap]::CreateCopyFromBuffer($buffer, [Windows.Graphics.Imaging.BitmapPixelFormat]::Bgra8, [int]$transform.ScaledWidth, [int]$transform.ScaledHeight, [Windows.Graphics.Imaging.BitmapAlphaMode]::Ignore)
            } else {
                $bitmap = Await ($decoder.GetSoftwareBitmapAsync([Windows.Graphics.Imaging.BitmapPixelFormat]::Bgra8, [Windows.Graphics.Imaging.BitmapAlphaMode]::Ignore, $transform, [Windows.Graphics.Imaging.ExifOrientationMode]::RespectExifOrientation, [Windows.Graphics.Imaging.ColorManagementMode]::DoNotColorManage)) ([Windows.Graphics.Imaging.SoftwareBitmap])
            }
            $result = Await ($script:engine.RecognizeAsync($bitmap)) ([Windows.Media.Ocr.OcrResult])
            return (@($result.Lines | ForEach-Object Text) -join "`n")
        } finally { if ($bitmap) { $bitmap.Dispose() } }
    }
    function ReadPage($stream, [int]$number, [int]$frameIndex = 0) {
        $stream.Seek(0)
        $decoder = Await ([Windows.Graphics.Imaging.BitmapDecoder]::CreateAsync($stream)) ([Windows.Graphics.Imaging.BitmapDecoder])
        if ($frameIndex -gt 0) { $decoder = Await ($decoder.GetFrameAsync($frameIndex)) ([Windows.Graphics.Imaging.BitmapFrame]) }
        # Scale small scans; split large maps instead of erasing detail by shrinking them.
        $max = [Math]::Min(2500, [Windows.Media.Ocr.OcrEngine]::MaxImageDimension)
        $width = [int]$decoder.OrientedPixelWidth; $height = [int]$decoder.OrientedPixelHeight
        if ([long]$width*$height -gt 64000000) { throw 'A imagem excede 64 milhoes de pixels. Reduza a resolucao antes de executar o OCR.' }
        $scale = [Math]::Min(2.0, [Math]::Max(1.0, 1800.0/[Math]::Max($width,$height)))
        $scaledW = [int][Math]::Round($width*$scale); $scaledH = [int][Math]::Round($height*$scale)
        $step = $max-64
        $columns = [int][Math]::Max(1, [Math]::Ceiling(($scaledW-$max)/$step)+1)
        $rows = [int][Math]::Max(1, [Math]::Ceiling(($scaledH-$max)/$step)+1)
        $texts = New-Object 'System.Collections.Generic.List[string]'; $tiles=0
        for ($row=0; $row -lt $rows; $row++) {
            $y = [int][Math]::Min($row*$step,[Math]::Max(0,$scaledH-$max))
            for ($column=0; $column -lt $columns; $column++) {
                $x = [int][Math]::Min($column*$step,[Math]::Max(0,$scaledW-$max))
                if ($tiles -ge $MaxTiles) { break }
                $tiles++
                $transform = New-Object Windows.Graphics.Imaging.BitmapTransform
                $transform.ScaledWidth=$scaledW; $transform.ScaledHeight=$scaledH
                $bounds = New-Object Windows.Graphics.Imaging.BitmapBounds
                $bounds.X=$x; $bounds.Y=$y; $bounds.Width=[Math]::Min($max,$scaledW-$x); $bounds.Height=[Math]::Min($max,$scaledH-$y)
                $transform.Bounds=$bounds
                $best=''; $bestScore=-1; $bestRotation=0
                foreach ($rotation in @(0,1,2,3)) {
                    $transform.Rotation=[Windows.Graphics.Imaging.BitmapRotation]$rotation
                    $rotatedBounds = New-Object Windows.Graphics.Imaging.BitmapBounds
                    switch ($rotation) {
                        0 { $rotatedBounds=$bounds }
                        1 { $rotatedBounds.X=$scaledH-$y-$bounds.Height; $rotatedBounds.Y=$x; $rotatedBounds.Width=$bounds.Height; $rotatedBounds.Height=$bounds.Width }
                        2 { $rotatedBounds.X=$scaledW-$x-$bounds.Width; $rotatedBounds.Y=$scaledH-$y-$bounds.Height; $rotatedBounds.Width=$bounds.Width; $rotatedBounds.Height=$bounds.Height }
                        3 { $rotatedBounds.X=$y; $rotatedBounds.Y=$scaledW-$x-$bounds.Width; $rotatedBounds.Width=$bounds.Height; $rotatedBounds.Height=$bounds.Width }
                    }
                    $transform.Bounds=$rotatedBounds
                    $candidate = Recognize $decoder $transform
                    $score = Score $candidate
                    if ($score -gt $bestScore) { $best=$candidate; $bestScore=$score; $bestRotation=$rotation }
                }
                # A second contrast pass only on complete upright images (no cropped buffer mismatch).
                if ($x -eq 0 -and $y -eq 0 -and $scaledW -le $max -and $scaledH -le $max -and $bestRotation -eq 0) {
                    $transform.Rotation=[Windows.Graphics.Imaging.BitmapRotation]::None
                    $transform.Bounds=$bounds
                    $candidate = Recognize $decoder $transform -Enhanced
                    if ((Score $candidate) -gt $bestScore) { $best=$candidate }
                }
                foreach ($line in ($best -split "`n")) {
                    if ($line.Trim() -and (($rows*$columns -eq 1) -or -not $texts.Contains($line))) { $texts.Add($line) }
                }
            }
        }
        if (($rows*$columns) -gt $MaxTiles) { $script:warnings.Add("Pagina ${number}: leitura limitada a $MaxTiles recortes.") }
        return @{ numero=$number; texto=($texts -join "`n"); recortes=$tiles }
    }
    $fullPath = [System.IO.Path]::GetFullPath($ImagePath)
    if (-not (Test-Path -LiteralPath $fullPath -PathType Leaf)) { throw 'Ficheiro nao encontrado.' }
    if ((Get-Item -LiteralPath $fullPath).Length -gt 52428800) { throw 'O ficheiro excede o limite de 50 MB.' }
    $file = Await ([Windows.Storage.StorageFile]::GetFileFromPathAsync($fullPath)) ([Windows.Storage.StorageFile])
    if ($Format -eq 'auto') { $Format = if ([IO.Path]::GetExtension($fullPath) -eq '.pdf') { 'pdf' } else { 'image' } }
    $pages=New-Object 'System.Collections.Generic.List[object]'; $total=1
    if ($Format -eq 'pdf') {
        $pdf = Await ([Windows.Data.Pdf.PdfDocument]::LoadFromFileAsync($file)) ([Windows.Data.Pdf.PdfDocument])
        $total=[int]$pdf.PageCount
        for ($i=0; $i -lt [Math]::Min($total,$MaxPages); $i++) {
            $page=$pdf.GetPage($i); $stream=New-Object Windows.Storage.Streams.InMemoryRandomAccessStream
            try {
                $options=New-Object Windows.Data.Pdf.PdfPageRenderOptions
                $ratio=[Math]::Min(3.125,[Math]::Sqrt(16000000.0/($page.Size.Width*$page.Size.Height)))
                $options.DestinationWidth=[int]($page.Size.Width*$ratio); $options.DestinationHeight=[int]($page.Size.Height*$ratio)
                AwaitAction ($page.RenderToStreamAsync($stream,$options))
                $stream.Seek(0)
                $pages.Add((ReadPage $stream ($i+1)))
            } finally { $stream.Dispose(); $page.Dispose() }
        }
        if ($total -gt $MaxPages) { $script:warnings.Add("Documento com $total paginas; foram lidas as primeiras $MaxPages paginas.") }
    } else {
        $stream = Await ($file.OpenAsync([Windows.Storage.FileAccessMode]::Read)) ([Windows.Storage.Streams.IRandomAccessStream])
        try {
            $decoder = Await ([Windows.Graphics.Imaging.BitmapDecoder]::CreateAsync($stream)) ([Windows.Graphics.Imaging.BitmapDecoder])
            $total=[int]$decoder.FrameCount
            for ($i=0; $i -lt [Math]::Min($total,$MaxPages); $i++) { $pages.Add((ReadPage $stream ($i+1) $i)) }
            if ($total -gt $MaxPages) { $script:warnings.Add("Imagem com $total paginas; foram lidas as primeiras $MaxPages paginas.") }
        } finally { $stream.Dispose() }
    }
    $text=(@($pages | ForEach-Object { $_.texto }) -join "`n`n").Trim()
    $output=@{ sucesso=($text.Length -gt 0); texto=$text; paginas=$total; paginas_processadas=$pages.Count; idioma=$selected.LanguageTag; avisos=$script:warnings.ToArray(); detalhes=$pages.ToArray(); motor='windows-native' }
    if ($Json) { $output | ConvertTo-Json -Depth 7 -Compress } else { Write-Output $text }
} catch {
    if ($Json) { @{sucesso=$false; texto=''; mensagem=$_.Exception.Message; motor='windows-native'} | ConvertTo-Json -Compress }
    else { [Console]::Error.WriteLine($_.Exception.Message) }
    exit 1
}
