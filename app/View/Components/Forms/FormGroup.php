<?php

namespace App\View\Components\Cms\Forms;

use Illuminate\View\Component;

class FormGroup extends Component
{
    public $name;
    public $title;
    public $required;
    public $border;
    /**
     * Create a new component instance.
     *
     * @return voids
     */
    public function __construct($name = null, $title = null, $required = null, $border = null)
    {
        $this->name = $name;
        $this->title = $title ? $title : $name;
        $this->required = $required;
        $this->border = !is_null($border) ? true : false;
       
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        // dd(gettype($this->required));
        return view('components.forms.form-group', [
            'name' => $this->name,
            'title' => $this->title,
            'border' => $this->border,
            'required' => $this->required]);
    }
}
