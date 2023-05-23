<?php

namespace Svanthuijl\Routable\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use ReflectionClass;
use Svanthuijl\Routable\Traits\InteractsWithRoutes;

class GenerateRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'routable-models:generate {model} {start?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command generates all routes for a given model';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        // Retrieve model argument
        $model = $this->argument('model');
        $start = $this->argument('start');

        // Check if the model exists
        if (!class_exists($model))
        {
            $this->error('"' . $model . '" does not exist.');
            return;
        }

        // Check if this is a routable model
        $modelTraits = (new ReflectionClass($model))->getTraits();
        if (!isset($modelTraits[InteractsWithRoutes::class]))
        {
            $this->error('"' . $model . '" does not implement "' . InteractsWithRoutes::class . '".');
            return;
        }


        // Configure batch limit
        $limit = 100;

        // Create routes
        if ($start !== null)
        {
            $models = $model::orderBy('id')
                ->offset($start)
                ->take($limit)
                ->get();

            // Iterate
            foreach ($models as $model)
                $model::class::updateOrCreateRoutes($model);

            return;
        }

        // Create batches
        $modelCount = $model::count();
        for ($start = 0; $start <= $modelCount; $start += $limit)
        {
            // Queue command
            Artisan::queue('routable-models:generate', [
                'model' => $model,
                'start' => $start,
            ]);

            $end = $start + $limit;
            if ($end > $modelCount)
                $end = $modelCount;

            $this->info('Batch created for "' . $model . '" from ' . $start . ' until ' . $end . '.');
        }
    }
}
