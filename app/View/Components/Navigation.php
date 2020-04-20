<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Navigation extends Component
{
    public $active;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($active = null)
    {
      $this->active = $active;
    }

    public function isActive($index) {
      return $index === $this->active;
    }

    public function activeClass($index) {
      return $this->isActive($index) ? " navigation__item_active" : "";
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.navigation');
    }
}
