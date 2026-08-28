<?php

namespace App\Models\Concerns;

use App\Services\DocumentoAutenticidadeService;

trait ComAutenticidadeQr
{
    /**
     * Evento: quando modelo é criado, gera QR token automaticamente
     * Convenção correta do Laravel para traits: boot{TraitName}()
     */
    public static function bootComAutenticidadeQr(): void
    {
        static::creating(function ($model) {
            if (empty($model->qr_token)) {
                $model->qr_token = DocumentoAutenticidadeService::gerarToken();
            }
        });
    }

    /**
     * Gera novo token QR (manual)
     * @return string
     */
    public function gerarQrToken(): string
    {
        $this->qr_token = DocumentoAutenticidadeService::gerarToken();
        $this->save();
        return $this->qr_token;
    }

    /**
     * Obtém URL de verificação
     * @return string
     */
    public function obterUrlVerificacao(): string
    {
        return DocumentoAutenticidadeService::gerarUrlQrSimples($this->qr_token);
    }

    /**
     * Obtém dados estruturados do QR
     * @return array
     */
    public function obterDadosQr(): array
    {
        return DocumentoAutenticidadeService::gerarDadosQr(
            $this->qr_token,
            $this->getTipoDocumento(),
            $this->id,
            $this->obterNomeDocumento()
        );
    }

    /**
     * Obtém tipo do documento (deve ser implementado no model)
     * @return string
     */
    protected function getTipoDocumento(): string
    {
        return class_basename(static::class);
    }

    /**
     * Obtém nome do documento (deve ser implementado no model se necessário)
     * @return string|null
     */
    protected function obterNomeDocumento(): ?string
    {
        return $this->nome_original ?? $this->nome ?? null;
    }

    /**
     * Marca como verificado
     * @return bool
     */
    public function marcarComoVerificado(): bool
    {
        return $this->update(['verificado' => true]);
    }

    /**
     * Verifica se está autenticado
     * @return bool
     */
    public function estaAutenticado(): bool
    {
        return !empty($this->qr_token) && $this->verificado;
    }
}
