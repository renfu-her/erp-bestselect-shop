<?php

namespace App\View\Components\Backend\Forms;

use Illuminate\View\Component;

class Input extends Component
{
    public $name;
    public $type;
    public $title;
    public $prepend;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($name='', $title='', $type=null, $prepend='', $append='', $help='')
    {
        $this->name = $name;
        $this->type = $type ? $type : 'text';
        $this->title = $title ? $title : $name;
        $this->prepend = $prepend;
        $this->append = $append;
        $this->help = $help;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.forms.input', [
            'name' => $this->name,
            'type' => $this->type,
            'title' => $this->title,
            'prepend' => $this->prepend,
            'append' => $this->append,
            'help' => $this->help,
        ]);
    }
}
