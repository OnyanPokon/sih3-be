<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BuatModulCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // TAMBAHKAN OPSI --type dengan validasi (wajib diisi)
    protected $signature = 'make:module {name} {--type= : The type of module (central or tenant)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Controller, Resource, Request, Service, and Model for a specific module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name'); // e.g., 'ProdukKategori'

        $tableName = Str::snake(Str::plural($name)); // 'ProdukKategori' -> 'produk_kategoris'
        $nameLc = strtolower(preg_replace('/(?<!^)[A-Z]/', ' $0', $name)); // 'ProdukKategori' -> 'produk kategori'

        $controllerName = "{$name}Controller";
        $serviceName = "{$name}Service";
        $resourceName = "{$name}Resource";
        $requestName = "{$name}Request";
        $variableService = lcfirst($serviceName); // 'produkKategoriService'

        $modelNamespace = "App\\Models\\{$name}";
        $serviceNamespace = "App\\Http\\Services";
        $controllerNamespace = "App\\Http\\Controllers";
        $resourceNamespace = "App\\Http\\Resources";
        $requestNamespace = "App\\Http\\Requests";

        // --- Generate Model & Migration ---
        $this->call('make:model', ['name' => "{$name}", '-m']);

        $this->info("Model {$name} and its migration created successfully.");

        // --- Generate Service ---
        $servicePath = app_path("Http/Services/{$serviceName}.php");
        File::makeDirectory(dirname($servicePath), 0755, true, true);
        $serviceStub = File::get(base_path('stubs/custom-service.stub'));
        $serviceContent = str_replace(
            ['{{ namespace }}', '{{ serviceName }}', '{{ modelNamespace }}', '{{ modelName }}'],
            [$serviceNamespace, $serviceName, $modelNamespace, $name],
            $serviceStub
        );
        File::put($servicePath, $serviceContent);
        $this->info("Service {$serviceName} created successfully.");

        // --- Generate Controller ---
        $controllerPath = app_path("Http/Controllers/{$controllerName}.php");
        File::makeDirectory(dirname($controllerPath), 0755, true, true);
        $controllerStub = File::get(base_path('stubs/custom-controller.stub'));
        $controllerContent = str_replace(
            ['{{ namespace }}', '{{ controllerName }}', '{{ serviceNamespace }}', '{{ serviceName }}', '{{ resourceNamespace }}', '{{ resourceName }}', '{{ requestNamespace }}', '{{ requestName }}', '{{ variableService }}', '{{ name_lc }}'],
            [$controllerNamespace, $controllerName, $serviceNamespace, $serviceName, $resourceNamespace, $resourceName, $requestNamespace, $requestName, $variableService, $nameLc],
            $controllerStub
        );
        File::put($controllerPath, $controllerContent);
        $this->info("Controller {$controllerName} created successfully.");

        // --- Generate Resource & Request ---
        $this->call('make:resource', ['name' => "{$resourceName}"]);
        $this->call('make:request', ['name' => "{$requestName}"]);
        $this->info("Resource and Request created successfully.");

        $this->info("All files for module '{$name}' generated successfully! ✅");
        return 0;
    }
}
