<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;


class BuatServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Service for a specific module';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $serviceName = "{$name}Service";
        $serviceNamespace = "App\\Http\\Services";

        $servicePath = app_path("Http/Services/{$serviceName}.php");
        if (!File::exists(app_path('Http/Services'))) {
            File::makeDirectory(app_path('Http/Services'), 0755, true);
        }

        // Generate Service with Stub
        $serviceStubPath = base_path('stubs/custom-service.stub');
        if (!File::exists($serviceStubPath)) {
            $this->error("Stub for service not found: {$serviceStubPath}");
            return 1; // Return error status
        }

        $serviceStub = File::get($serviceStubPath);
        $stub = file_get_contents(base_path('stubs/custom-service.stub'));
        $serviceContent = str_replace(
            ['{{ serviceName }}', '{{ modelName }}', '{{ namespace }}'],
            [$serviceName, $name, $serviceNamespace],
            $serviceStub
        );
        File::put($servicePath, $serviceContent);
        $this->info("Service {$serviceName} created successfully.");
    }
}
