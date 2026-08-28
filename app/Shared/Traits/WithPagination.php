<?php

namespace App\Shared\Traits;

/**
 * Trait para adicionar paginação padronizada a componentes Livewire.
 *
 * Fornece métodos para gerenciar paginação, ordenação e itens por página.
 */
trait WithPagination
{
    public int $perPage = 15;
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public int $page = 1;

    /**
     * Define quantos itens são exibidos por página.
     */
    public function setPerPage(int $perPage): void
    {
        $this->perPage = max(1, min($perPage, 100)); // Min 1, Max 100
        $this->resetPage();
    }

    /**
     * Ordena os registros.
     */
    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    /**
     * Reseta para a primeira página.
     */
    public function resetPage(): void
    {
        $this->page = 1;
    }

    /**
     * Retorna o ícone de ordenação para uma coluna.
     */
    public function getSortIcon(string $column): string
    {
        if ($this->sortBy !== $column) {
            return '';
        }

        return $this->sortDirection === 'asc' ? '↑' : '↓';
    }

    /**
     * Retorna a classe CSS para indicar ordenação.
     */
    public function getSortClass(string $column): string
    {
        return $this->sortBy === $column ? 'text-primary font-bold' : '';
    }
}
