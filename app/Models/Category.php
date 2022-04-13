<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Category extends Model
{
    use HasFactory;
    protected $table = 'prd_categorys';
    protected $guarded = [];

    public function storeNewCategory(Request $request)
    {
        $request->validate([
            'category' => [
                'required',
                'string',
                'unique:App\Models\Category',
            ]
        ]);

        Category::create(['category' => $request->input('category')]);
    }

    public function findCategoryById(int $id)
    {
        return Category::find($id)->category;
    }

    public function updateCategory(Request $request, int $id)
    {
        $request->validate([
            'category' => [
                'required',
                'string',
                'unique:App\Models\Category',
            ]
        ]);

        Category::where('id', '=', $id)
                ->update(['category' => $request->input('category')]);
    }

    public function destroyById(int $id)
    {
        Category::destroy($id);
    }
}
