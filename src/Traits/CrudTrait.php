<?php 

namespace iamx\ApiCrudGenerator\Traits;

trait CrudTrait {

    protected function makeCrud($name)
    {
        $this->makeMigration($name);
        $this->makeModel($name);
        $this->makeRequest($name);
        $this->makeTransformer($name);
        $this->makeController($name);
        $this->addApiRoute($name);
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
        if(!file_exists(app_path("/Transformers")))
            mkdir(app_path("/Transformers"), 0777, true);

    	$modelTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNameSingularLowerCase}}',
            ],
            [
                $name,
                strtolower($name),
            ],
            $this->getStub('Transformer')
        );
        
        file_put_contents(app_path("/Transformers/{$name}Transformer.php"), $modelTemplate);
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
    }

    /**
     * Add a route to Api Routes
     *
     * @author Benito Huerta
     * @created 2020/06/06
     * @param string $name
     */
    protected function addApiRoute($name)
    {
        $path  = base_path('routes/api.php');
        $line  = PHP_EOL . 'Route::resource(\''; 
        $line .= \Str::plural(strtolower($name)); 
        $line .= "', 'Api\\{$name}\\{$name}Controller'"; 
        $line .= ", ['except' => ['create', 'edit']]";
        $line .= ");";

        \File::append($path, $line);
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