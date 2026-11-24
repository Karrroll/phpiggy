<?php

declare(strict_types=1);

namespace Framework;

class TemplateEngine
{
  private array $globalTemplateData = [];

  //set a mandatory parameter for the TemplateEngine object.
  //it should indicate the path to the templates (views) directory.
  public function __construct(private string $basePath)
  {
  }

  public function render(string $template, array $data = [])
  {
    // Convert array keys into variables
    // e.g. $data = ['a' => 1] becomes $a = 1
    // This allows use $a instead of $this->data['a'] in the $template
    extract($data, EXTR_SKIP);
    extract($this->globalTemplateData, EXTR_SKIP);

    ob_start();

    include $this->resolve($template);

    $output = ob_get_contents();

    ob_end_clean();

    return $output;
  }

  public function resolve(string $path)
  {
    return "{$this->basePath}/{$path}";
  }

  // Method to add a global variable e.g. ['title' => 'Home Page']
  // extract() in the render() method allows use $title instead of $this->globalTemplateData['title'] in the $template
  public function addGlobal(string $key, mixed $value) {
    $this->globalTemplateData[$key] = $value;
  }
}