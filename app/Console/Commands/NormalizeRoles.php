<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Support\RoleCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class NormalizeRoles extends Command
{
    protected $signature = 'roles:normalize';

    protected $description = 'Unifica códigos de roles existentes según el catálogo canónico.';

    public function handle(): int
    {
        $definitions = RoleCatalog::definitions();

        foreach ($definitions as $code => $definition) {
            $role = Role::firstOrCreate(
                ['code' => $code],
                Arr::only($definition, ['name', 'category', 'is_featured', 'position'])
            );

            foreach ($definition['aliases'] as $alias) {
                if ($alias === $code) {
                    continue;
                }

                $aliases = Role::where('code', $alias)->get();

                foreach ($aliases as $aliasRole) {
                    DB::table('movie_people')
                        ->where('role_id', $aliasRole->id)
                        ->update(['role_id' => $role->id]);

                    $aliasRole->delete();
                }
            }
        }

        $this->info('Roles normalizados.');
        return self::SUCCESS;
    }
}
