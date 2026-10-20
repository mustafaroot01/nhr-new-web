<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected array $selectedPermissions = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['guard_name'] = 'web';
        $this->selectedPermissions = $this->collectPermissions($data);

        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, 'perm_') || $key === 'can_publish') {
                unset($data[$key]);
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncPermissions($this->selectedPermissions);
    }

    private function collectPermissions(array $data): array
    {
        $permissions = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'perm_') && is_array($value)) {
                $permissions = array_merge($permissions, $value);
            }
        }

        if (! empty($data['can_publish'])) {
            $permissions[] = 'publish_posts';
        }

        // Only keep permissions that actually exist
        return Permission::whereIn('name', $permissions)->pluck('name')->toArray();
    }
}
