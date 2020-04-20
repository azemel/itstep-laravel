<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FormField extends Component
{
    public $key;
    public $name;
    public $value;
    public $type;

    public function test() {
      return "TEST";
    }

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($key, $name, $value, $type = "text")
    {
      $this->key = $key;
      $this->name = $name;
      $this->value = $value;
      $this->type = $type;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
      return view('components.form-field');
    }
}
