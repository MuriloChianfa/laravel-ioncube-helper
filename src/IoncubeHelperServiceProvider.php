<?php

declare(strict_types=1);

namespace MuriloChianfa\LaravelIoncubeHelper;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

/**
 * Provide a basic package configuration.
 *
 * @method boot
 * @method register
 */
final class IoncubeHelperServiceProvider extends ServiceProvider
{
    const IONCUBE_CALLBACK_FILE = 'public/ioncube.php';
    const CALLBACK_FILE = __DIR__ . '/../' . self::IONCUBE_CALLBACK_FILE;

    const POSTUP_MIGRATION_SCRIPT = 'storage/migrate.sh';
    const MIGRATION_SCRIPT = __DIR__ . '/../' . self::POSTUP_MIGRATION_SCRIPT;

    const LICENSE_EXCEPTION_FILES = 'views/vendor';
    const LICENSE_EXCEPTION_VIEWS = __DIR__ . '/../resources/views';

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            self::CALLBACK_FILE => base_path(self::IONCUBE_CALLBACK_FILE),
        ], 'public');

        $this->publishes([
            self::MIGRATION_SCRIPT => base_path(self::POSTUP_MIGRATION_SCRIPT),
        ], 'storage');

        $this->publishes([
            self::LICENSE_EXCEPTION_VIEWS => resource_path(self::LICENSE_EXCEPTION_FILES),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $loader = AliasLoader::getInstance();
        $loader->alias(\Illuminate\View\Compilers\BladeCompiler::class, \MuriloChianfa\LaravelIoncubeHelper\BladeCompiler::class);
    }
}
