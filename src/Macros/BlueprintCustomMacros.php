<?php

namespace LarawireGarage\SimpleMultitenancy\Macros;

use Illuminate\Database\Schema\Blueprint;

class BlueprintCustomMacros
{
    public function __invoke()
    {
        Blueprint::macro('userBy', function ($hasSoftDeletes = true) {
            /** @var Blueprint $this */
            $this->foreignId(config('mutitenancy.column_created', 'created_by'))->nullable();
            $this->foreignId(config('mutitenancy.column_updated', 'updated_by'))->nullable();
            if ($hasSoftDeletes) $this->foreignId(config('mutitenancy.column_deleted', 'deleted_by'))->nullable();
        });
    }
}
