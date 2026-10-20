<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Permission;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected array $selectedPermissions = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->hidden(fn () => $this->record->name === 'admin'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $names = $this->record->permissions->pluck('name')->toArray();

        $grouped = [];
        foreach ($names as $name) {
            if ($name === 'publish_posts') {
                $data['can_publish'] = true;

                continue;
            }
            // name format: {ability}_{resource}
            $parts = explode('_', $name, 2);
            if (count($parts) === 2) {
                $resource = $parts[1];
                $grouped["perm_{$resource}"][] = $name;
            }
        }

        return array_merge($data, $grouped);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->selectedPermissions = $this->collectPermissions($data);

        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, 'perm_') || $key === 'can_publish') {
                unset($data[$key]);
            }
        }

        return $data;
    }

    protected function afterSave(): void
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

        return Permission::whereIn('name', $permissions)->pluck('name')->toArray();
    }
}
