<?php

namespace App\Http\Livewire\Nacional;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

class AdministracaoNacional extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = 'all';
    public $filterStatus = 'all';
    public $showModal = false;
    public $editingTenant = null;
    public $tenantData = [
        'name' => '',
        'code' => '',
        'type' => 'provincial',
        'is_active' => true,
        'database_host' => '',
        'database_port' => '',
        'database_name' => '',
        'database_username' => '',
        'database_password' => '',
    ];

    protected $rules = [
        'tenantData.name' => 'required|string|max:255',
        'tenantData.code' => 'required|string|max:10|unique:tenants,code',
        'tenantData.type' => 'required|in:central,provincial',
        'tenantData.is_active' => 'boolean',
        'tenantData.database_host' => 'required|string',
        'tenantData.database_port' => 'required|integer',
        'tenantData.database_name' => 'required|string',
        'tenantData.database_username' => 'required|string',
        'tenantData.database_password' => 'required|string',
    ];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        // Carregar dados iniciais se necessário
    }

    public function createTenant(): void
    {
        $this->validate();

        Tenant::create([
            'name' => $this->tenantData['name'],
            'code' => $this->tenantData['code'],
            'type' => $this->tenantData['type'],
            'is_active' => $this->tenantData['is_active'],
            'database_host' => $this->tenantData['database_host'],
            'database_port' => $this->tenantData['database_port'],
            'database_name' => $this->tenantData['database_name'],
            'database_username' => $this->tenantData['database_username'],
            'database_password' => $this->tenantData['database_password'],
            'sync_status' => 'pending',
        ]);

        $this->showModal = false;
        $this->reset('tenantData');
        $this->dispatch('tenant-created');
    }

    public function editTenant(Tenant $tenant): void
    {
        $this->editingTenant = $tenant;
        $this->tenantData = [
            'name' => $tenant->name,
            'code' => $tenant->code,
            'type' => $tenant->type,
            'is_active' => $tenant->is_active,
            'database_host' => $tenant->database_host,
            'database_port' => $tenant->database_port,
            'database_name' => $tenant->database_name,
            'database_username' => $tenant->database_username,
            'database_password' => $tenant->database_password,
        ];
        $this->showModal = true;
    }

    public function updateTenant(): void
    {
        $this->validate([
            'tenantData.name' => 'required|string|max:255',
            'tenantData.code' => 'required|string|max:10|unique:tenants,code,' . $this->editingTenant->id,
            'tenantData.type' => 'required|in:central,provincial',
            'tenantData.is_active' => 'boolean',
        ]);

        $this->editingTenant->update([
            'name' => $this->tenantData['name'],
            'code' => $this->tenantData['code'],
            'type' => $this->tenantData['type'],
            'is_active' => $this->tenantData['is_active'],
        ]);

        if (!empty($this->tenantData['database_host'])) {
            $this->editingTenant->update([
                'database_host' => $this->tenantData['database_host'],
                'database_port' => $this->tenantData['database_port'],
                'database_name' => $this->tenantData['database_name'],
                'database_username' => $this->tenantData['database_username'],
                'database_password' => $this->tenantData['database_password'],
            ]);
        }

        $this->showModal = false;
        $this->editingTenant = null;
        $this->reset('tenantData');
        $this->dispatch('tenant-updated');
    }

    public function deleteTenant(Tenant $tenant): void
    {
        if ($tenant->isCentral()) {
            $this->addError('error', 'Não é possível eliminar o tenant central');
            return;
        }

        $tenant->delete();
        $this->dispatch('tenant-deleted');
    }

    public function toggleTenantStatus(Tenant $tenant): void
    {
        $tenant->update(['is_active' => !$tenant->is_active]);
    }

    public function getTenantsProperty()
    {
        $query = Tenant::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterType !== 'all') {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus !== 'all') {
            if ($this->filterStatus === 'active') {
                $query->where('is_active', true);
            } elseif ($this->filterStatus === 'inactive') {
                $query->where('is_active', false);
            }
        }

        return $query->orderBy('code')->paginate(10);
    }

    public function render()
    {
        return view('livewire.nacional.administracao-nacional', [
            'tenants' => $this->tenants,
        ])->layout('layouts.app');
    }
}
