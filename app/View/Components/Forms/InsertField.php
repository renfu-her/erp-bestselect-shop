<?php

namespace App\View\Components\Backend\Forms;

use Illuminate\View\Component;

class InsertField extends Component
{
    public $title;
    public $placehorder;
    public $btnTitle;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($title = null, $placehorder = null, $btnTitle = null)
    {
        $this->title = $title ? $title : '標題';
        $this->placehorder = $placehorder ? $placehorder : '例:';
        $this->btnTitle = $btnTitle ? $btnTitle : '儲存';

    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.forms.insert-field', ['title' => $this->title, 'placehorder' => $this->placehorder, 'btnTitle' => $this->btnTitle]);
    }
}
