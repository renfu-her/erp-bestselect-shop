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
        $dataList = Category::All();

        return view('cms.settings.category.list', [
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
    public function store(Request $request, Category $category)
    {
        $category->storeNewCategory($request);

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
    public function edit(int $id, Category $category)
    {
        return view('cms.settings.category.edit', [
            'method' => 'edit',
            'formAction' => Route('cms.category.edit', ['id' => $id]),
            'category' => $category->findCategoryById($id),
            'id' => $id,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCategoryRequest  $request
     *
     * @return Response
     */
    public function update(Request $request, int $id, Category $category)
    {
        $category->updateCategory($request, $id);

        return redirect(Route('cms.category.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(int $id, Category $category)
    {
        $category->destroyById($id);

        return redirect(Route('cms.category.index'));
    }
}
