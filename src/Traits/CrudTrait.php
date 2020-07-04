<?php 

namespace iamx\ApiCrudGenerator\Traits;

trait CrudTrait {

    /**
     * Make a api crud
     * 
     * @author Benito Huerta
     * @created 2020/06/06
     * @param $name string
     */
    protected function makeCrud($name)
    {
        if(!$this->exists('Migration', $name))
            $this->makeMigration($name);
        else
            $this->error(\Str::plural($name) . ' Migration already exists!');

        if(!$this->exists('Model', $name))
            $this->makeModel($name);
        else
            $this->error($name . ' Model already exists!');

        if(!$this->exists('Request', $name))
            $this->makeRequest($name);
        else
            $this->error($name . ' Request already exists!');

        if(!$this->exists('Transformer', $name))
            $this->makeTransformer($name);
        else
            $this->error($name . ' Transformer already exists!');

        if(!$this->exists('Controller', $name))
            $this->makeController($name);
        else
            $this->error($name . ' Controller already exists!');

        $this->addApiRoutes($name);
    }

    /**
     * Verify file exists
     *
     * @author Benito Huerta
     * @created 2020/06/06
     * @param $type string
     * @param $name string
     * @return bool
     */
    protected function exists($type, $name)
    {
        return [
            'Migration'   => count(glob(base_path("database/migrations/*_create_" . \Str::plural(strtolower($name)) . "_table.php"))) > 0 ? true : false,
            'Model'       => file_exists(app_path("/{$name}.php")),
            'Request'     => file_exists(app_path("/Http/Requests/{$name}Request.php")),
            'Transformer' => file_exists(app_path("/Transformers/{$name}Transformer.php")),
            'Controller'  => file_exists(app_path("/Http/Controllers/Api/{$name}"))
        ][$type];
    }

	/**
	 * Create a new Model based on Model stub
     *
	 * @author Benito Huerta
     * @created 2020/06/06
     * @param string $name
	 */
	protected function makeModel($name)
    {
        try {
            
            $modelTemplate = str_replace(
                [
                    '{{modelName}}',
                    '{{modelNamePluralLowerCase}}',
                ],
                [
                    $name,
                    strtolower(\Str::plural($name)),
                ],
                $this->getStub('Model')
            );
            
            file_put_contents(app_path("/{$name}.php"), $modelTemplate);
            $this->info('New ' . $name . ' Model created!');

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Create a new Request based on Request stub
     *
     * @author Benito Huerta
     * @created 2020/06/06
     * @param string $name
     */
    protected function makeRequest($name)
    {
        try {

            if(!file_exists(app_path("/Http/Requests")))
                mkdir(app_path("/Http/Requests"), 0777, true);

            $modelTemplate = str_replace(
                [
                    '{{modelName}}',
                ],
                [
                    $name,
                ],
                $this->getStub('Request')
            );
            
            file_put_contents(app_path("/Http/Requests/{$name}Request.php"), $modelTemplate);
            $this->info('New ' . $name . ' Request created!');

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
	 * Create a new Transformer based on Transformer stub
     *
	 * @author Benito Huerta
     * @created 2020/06/06
     * @param string $name
	 */
    protected function makeTransformer($name) 
    {
        try {

            if(!file_exists(app_path("/Transformers")))
                mkdir(app_path("/Transformers"), 0777, true);

            $modelTemplate = str_replace(
                [
                    '{{modelName}}',
                    '{{modelNameSingularLowerCase}}',
                    '{{modelNamePluralLowerCase}}',
                ],
                [
                    $name,
                    strtolower($name),
                    strtolower(\Str::plural($name)),
                ],
                $this->getStub('Transformer')
            );
            
            file_put_contents(app_path("/Transformers/{$name}Transformer.php"), $modelTemplate);
            $this->info('New ' . $name . ' Transformer created!');

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }   
    }

    /**
	 * Create a new Controller based on Controller stub
     *
	 * @author Benito Huerta
     * @created 2020/06/06
     * @param string $name
	 */
    protected function makeController($name)
    {
        try {

            if(!file_exists(app_path("/Http/Controllers/Api/{$name}")))
                mkdir(app_path("/Http/Controllers/Api/{$name}"), 0777, true);

            $controllerTemplate = str_replace(
                [
                    '{{modelName}}',
                    '{{modelNamePluralLowerCase}}',
                    '{{modelNameSingularLowerCase}}'
                ],
                [
                    $name,
                    strtolower(\Str::plural($name)),
                    strtolower($name)
                ],
                $this->getStub('Controller')
            );

            file_put_contents(app_path("/Http/Controllers/Api/{$name}/{$name}Controller.php"), $controllerTemplate);
            $this->info('New ' . $name . ' Controller created!');

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        } 
    }

    /**
	 * Create a new Migration based on Migration stub
     *
	 * @author Benito Huerta
     * @created 2020/06/06
     * @param string $name
	 */
    protected function makeMigration($name)
    {
        try {

            $migrationTemplate = str_replace(
                [
                    '{{modelName}}',
                    '{{modelNamePluralLowerCase}}',
                    '{{modelNamePlural}}'
                ],
                [
                    $name,
                    strtolower(\Str::plural($name)),
                    \Str::plural($name)
                ],
                $this->getStub('Migration')
            );

            file_put_contents(base_path("database/migrations/" . date("Y_m_d_Gis") . "_create_" . \Str::plural(strtolower($name)) . "_table.php"), $migrationTemplate);
            $this->info('New ' . \Str::plural($name) . ' Migration created!');

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        } 
    }

    /**
     * Add a route to Api Routes
     *
     * @author Benito Huerta
     * @created 2020/06/06
     * @param string $name
     */
    protected function addApiRoutes($name)
    {
        try {
            
            $path  = base_path('routes/api.php');
            $line  = PHP_EOL . 'Route::apiResource(\''; 
            $line .= \Str::plural(strtolower($name)); 
            $line .= "', 'Api\\{$name}\\{$name}Controller'"; 
            $line .= ");";

            if(!$this->routeExists($path, $name)) {

                \File::append($path, $line);
                $this->info(\Str::plural($name) . ' api routes added!');

            } else {
                $this->error(\Str::plural($name) . ' api routes already exists!');
            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Verify if route exists
     *
     * @author Benito Huerta
     * @created 2020/06/06
     * @param string $path
     * @param string $name
     */
    protected function routeExists($path, $route)
    {
        if(strpos(file_get_contents($path), $route)) 
           return true;

        return false;
    }

    /**
	 * Get Stub Type
     *
	 * @author Benito Huerta
     * @created 2020/06/06
     * @param string $type
	 */
    protected function getStub($type)
    {
        return file_get_contents(__DIR__ . "/../Stubs/$type.stub");
    }
}