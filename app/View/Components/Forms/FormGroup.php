<?php

namespace App\View\Components\Forms;

use Illuminate\View\Component;

class FormGroup extends Component
{
    public $name;
    public $title;
    public $required;
    public $border;
    public $class;
    public $help;
    /**
     * Create a new component instance.
     *
     * @return voids
     */
    public function __construct($name='', $title='', $required=false, $border=false, $class='', $help=null)
    {
        $this->name = $name;
        $this->title = $title ? $title : $name;
        $this->required = ($required == 'true');
        $this->border = ($border == 'true');
        $this->class = $class;
        $this->help = $help;
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
            'required' => $this->required,
            'class' => $this->class,
            'help' => $this->help
        ]);
    }
}
