<?php
declare(strict_types=1);

namespace Framework;

use ReflectionClass, ReflectionNamedType;
use Framework\Exceptions\ContainerException;

class Container {
  // Store exists definitions
  private array $definitions = [];
  // Store already instantiated objects
  private array $resolved = [];

  public function addDefinitions(array $newDefinitions) {

    //$this->definitions = array_merge($this->definitions, $newDefinitions);    same function as below
    $this->definitions = [...$this->definitions, ...$newDefinitions];
  }

  //Resolve means to find, determine, or identify something
  //The goal of this method is find a list of dependencies required by a $className
  public function resolve(string $className) {
    //Get $className information to $reflectionClass
    $reflectionClass = new ReflectionClass($className);

    //Check if $className can be instantiated
    if (!$reflectionClass->isInstantiable()) {
      throw new ContainerException("Class {$className} is not instantiable");
    }

    //Get $className constructor information to $constructor
    $constructor = $reflectionClass->getConstructor();

    //Check if the class requires a constructor. If no constructor create an object
    if(!$constructor) {
      return new $className;
    }

    //Get $className constructor parameters information to $params
    $params = $constructor->getParameters();

    //Check if the constructor has a parameters. If no create an object
    if(count($params) === 0) {
      return new $className;
    }

    //Iterate through constructor parameters to identify dependencies
    foreach($params as $param) {
      $name = $param->getName();
      $type = $param->getType();

      if(!$type) {
        throw new ContainerException("Failed to resolve class {$className} becouse param {$name} is missing a type hint.");
      }

      if(!$type instanceof ReflectionNamedType || $type->isBuiltIn()) {
        throw new ContainerException("Failed to resolve class {$className} becouse invalid param name.");
      }

      //Find the dependency and add it (an object) to the dependencies array
      $dependencies[] = $this->get($type->getName());
    }

    //Create and return instance of $className with the constructor parameters
    //e.g. new UserService($templateEngine);
    // where:
    // UserService is the class represented by $className
    // $templateEngine is an instance of the TemplateEngine class
    return $reflectionClass->newInstanceArgs($dependencies);
  }

  public function get(string $id) {
    // Check if the requested class is registered in the container
    // Check if the requested key: $id exists in the $definitions array
    if(!array_key_exists($id, $this->definitions)) {
      throw new ContainerException("Class {$id} does not exist in container.");
    }

    // Check if an instance of this class has already been created
    // If it exists, return the previously created instance instead of creating a new one
    // This ensures that the same instance is reused (Singleton pattern)

    //Check if the requested key: $id already exists in the $resolved array
    if(array_key_exists($id, $this->resolved)) {
      //Return the value stored under the $id key in the $resolved array
      //e.g. TemplateEngine::class => fn() => new TemplateEngine(Paths::VIEW)
      //if $id = TemplateEngine::class return new TemplateEngine(Paths::VIEW)

      // Return already created instance
      return $this->resolved[$id];
    }

    // Get the factory (anonymous function) registered for this class
    // The factory is responsible for creating the dependency
    // Example: TemplateEngine::class => fn() => new TemplateEngine(Paths::VIEW)

    //Assign the value stored under the $id key in the $definitions array
    $factory = $this->definitions[$id];

    // Execute the factory function to create the dependency
    // Pass the current Container object ($this) as an argument
    // The factory can use the container to resolve other dependencies if needed
    $dependency = $factory($this);

    // Store the newly created instance in the $resolved array
    // Use the class name ($id) as the key and the object as the value
    // This allows the container to reuse the same instance next time it is requested
    $this->resolved[$id] = $dependency;
    
    // Return the newly created dependency
    return $dependency;
  }
}

?>