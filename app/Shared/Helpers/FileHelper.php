<?php

namespace App\Shared\Helpers;

class FileHelper
{
    /**
     * Formata bytes para formato legível (KB, MB, GB).
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Obtém extensão do arquivo.
     */
    public static function getExtension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Verifica se é arquivo de imagem.
     */
    public static function isImage(string $filename): bool
    {
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        return in_array(self::getExtension($filename), $extensions);
    }

    /**
     * Verifica se é arquivo de documento.
     */
    public static function isDocument(string $filename): bool
    {
        $extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
        return in_array(self::getExtension($filename), $extensions);
    }

    /**
     * Verifica se é arquivo de mapa/GIS.
     */
    public static function isMapFile(string $filename): bool
    {
        $extensions = ['shp', 'geojson', 'kml', 'gml', 'tif', 'tiff', 'jp2'];
        return in_array(self::getExtension($filename), $extensions);
    }

    /**
     * Formata nome do arquivo removendo caracteres especiais.
     */
    public static function sanitizeFilename(string $filename): string
    {
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    }
}
