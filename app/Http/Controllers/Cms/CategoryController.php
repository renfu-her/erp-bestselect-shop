<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $dataList = Category::paginate(10)->appends($query);

        return view('cms.settings.category.index', [
            'dataList' => $dataList
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('cms.settings.category.edit', [
            'method' => 'create',
            'formAction' => Route('cms.category.create'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string'
        ]);

        Category::create(['category' => $request->input('category')]);

        return redirect(Route('cms.category.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  Category  $category
     *
     * @return Response
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     *
     * @return Response
     */
    public function edit(int $id)
    {
        $category = Category::find($id)->category;
        return view('cms.settings.category.edit', [
            'method' => 'edit',
            'formAction' => Route('cms.category.edit', ['id' => $id]),
            'category' => $category,
            'id' => $id,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCategoryRequest  $request
     * @param  Category  $category
     *
     * @return Response
     */
    public function update(Request $request, int $id)
    {
        //
        $request->validate([
            'category' => 'required|string'
        ]);

        Category::where('id', '=', $id)
            ->update(['category' => $request->input('category')]);

        return redirect(Route('cms.category.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Category  $category
     *
     * @return Response
     */
    public function destroy(Category $category, int $id)
    {
        $category::destroy($id);
        return redirect(Route('cms.category.index'));
    }
}
