param(
    [Parameter(Mandatory=$true)]
    [string]$ImagePath
)

$fullPath = [System.IO.Path]::GetFullPath($ImagePath)
if (-not (Test-Path $fullPath)) {
    Write-Error "Ficheiro nao encontrado: $fullPath"
    exit 1
}

# Carregar assemblies WinRT
$null = [Windows.Storage.StorageFile, Windows.Storage, ContentType = WindowsRuntime]
$null = [Windows.Graphics.Imaging.BitmapDecoder, Windows.Graphics, ContentType = WindowsRuntime]
$null = [Windows.Media.Ocr.OcrEngine, Windows.Media, ContentType = WindowsRuntime]
$null = [Windows.Globalization.Language, Windows.Globalization, ContentType = WindowsRuntime]

# Helper em C# com types WinRT
$typeDef = @"
using System;
using System.IO;
using System.Threading.Tasks;
using Windows.Graphics.Imaging;
using Windows.Media.Ocr;
using Windows.Storage;

namespace GterrasOcr
{
    public static class Runner
    {
        public static string ExtractText(string filePath)
        {
            try
            {
                var task = StorageFile.GetFileFromPathAsync(Path.GetFullPath(filePath)).AsTask();
                task.Wait();
                var file = task.Result;

                var streamTask = file.OpenAsync(FileAccessMode.Read).AsTask();
                streamTask.Wait();
                using (var stream = streamTask.Result)
                {
                    var decTask = BitmapDecoder.CreateAsync(stream).AsTask();
                    decTask.Wait();
                    var decoder = decTask.Result;

                    var bmpTask = decoder.GetSoftwareBitmapAsync().AsTask();
                    bmpTask.Wait();
                    var bmp = bmpTask.Result;

                    var lang = new Windows.Globalization.Language("pt-PT");
                    var engine = OcrEngine.TryCreateFromLanguage(lang);
                    if (engine == null)
                    {
                        var langBr = new Windows.Globalization.Language("pt-BR");
                        engine = OcrEngine.TryCreateFromLanguage(langBr) ?? OcrEngine.TryCreateFromUserProfileLanguages();
                    }

                    if (engine == null) return string.Empty;

                    var ocrTask = engine.RecognizeAsync(bmp).AsTask();
                    ocrTask.Wait();
                    return ocrTask.Result.Text;
                }
            }
            catch (Exception ex)
            {
                return "ERROR: " + ex.Message;
            }
        }
    }
}
"@

try {
    Add-Type -ReferencedAssemblies System.Runtime.WindowsRuntime -TypeDefinition $typeDef -ErrorAction SilentlyContinue
    $output = [GterrasOcr.Runner]::ExtractText($fullPath)
    [Console]::OutputEncoding = [System.Text.Encoding]::UTF8
    Write-Output $output
} catch {
    Write-Error $_.Exception.Message
    exit 1
}
