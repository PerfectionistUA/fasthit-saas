<?php

namespace App\Observers;

use App\Models\Tenant;
use InvalidArgumentException;

class TenantObserver
{
    public function updating(Tenant $tenant): void
    {
        // 1) Якщо parent_id НЕ помінявся — нічого не робимо
        if (! $tenant->isDirty('parent_id')) {
            return;
        }

        $newParentId = $tenant->parent_id;

        // 2) Не може бути власним батьком
        if ($newParentId === $tenant->id) {
            throw new InvalidArgumentException('A tenant cannot be its own parent.');
        }

        // 3) Щоб не створити цикл — перевірити, що новий батько НЕ є нащадком цього тенанта
        if ($newParentId !== null) {
            $potentialParent = Tenant::find($newParentId);

            if ($potentialParent && $potentialParent->isDescendantOf($tenant->id)) {
                throw new InvalidArgumentException(
                    'Cyclic dependency detected: The chosen parent is already a descendant of this tenant.'
                );
            }
        }
    }
}
