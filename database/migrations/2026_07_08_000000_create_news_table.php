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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('title');
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->date('news_date');
            $table->string('featured_image_path')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['campus_id', 'status']);
            $table->index('news_date');
        });

        foreach (['view', 'create', 'update', 'delete'] as $action) {
            $slug = 'news.' . $action;
            $permission = Permission::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'resource' => 'news',
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
        Schema::dropIfExists('news');

        Permission::query()
            ->where('resource', 'news')
            ->whereIn('action', ['view', 'create', 'update', 'delete'])
            ->delete();
    }
};
