<?php

use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('designation');
            $table->string('profile_image')->nullable();
            $table->text('review');
            $table->unsignedTinyInteger('rating');
            $table->integer('display_order')->default(0);
            $table->boolean('featured')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['status', 'featured']);
            $table->index('display_order');
        });

        foreach (['view', 'create', 'update', 'delete'] as $action) {
            $slug = 'review.' . $action;
            $permission = Permission::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'resource' => 'review',
                    'action' => $action,
                    'description' => Str::headline($slug),
                ]
            );

            Role::query()
                ->whereIn('slug', ['owner', 'admin'])
                ->get()
                ->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching([$permission->id]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');

        Permission::query()
            ->where('resource', 'review')
            ->whereIn('action', ['view', 'create', 'update', 'delete'])
            ->delete();
    }
};
