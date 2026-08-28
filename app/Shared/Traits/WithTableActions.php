<?php

namespace App\Shared\Traits;

/**
 * Trait para adicionar ações comuns de tabela a componentes Livewire.
 *
 * Fornece métodos para delete, edit, view com confirmações e feedback.
 */
trait WithTableActions
{
    public ?int $selectedId = null;
    public bool $showDeleteModal = false;
    public bool $showDetailModal = false;
    public string $actionMessage = '';

    /**
     * Abre o modal de confirmação de deleção.
     */
    public function confirmDelete(int $id): void
    {
        $this->selectedId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Fecha o modal de deleção.
     */
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->selectedId = null;
    }

    /**
     * Abre o modal de detalhes.
     */
    public function showDetail(int $id): void
    {
        $this->selectedId = $id;
        $this->showDetailModal = true;
    }

    /**
     * Fecha o modal de detalhes.
     */
    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedId = null;
    }

    /**
     * Define a mensagem de ação (sucesso/erro).
     */
    public function setActionMessage(string $message, string $type = 'success'): void
    {
        $this->actionMessage = $message;
        // Limpar a mensagem após 5 segundos (pode ser feito via JS)
        $this->dispatch('action-message', message: $message, type: $type);
    }
}
