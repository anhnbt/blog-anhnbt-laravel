<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Category;

class CategoryController extends Controller
{
    
    /**
     * Create a new controller instance.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::whereNull('parent_id')
                                ->with('categories')
                                ->orderBy('id', 'desc')
                                ->paginate(10);
        return view('admin.category.index', ['categories' => $categories]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::whereNull('parent_id')
                                ->with('categories')
                                ->get();
        return view('admin.category.create', ['categories' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:categories|max:255',
            'thumbnail' => 'image|nullable|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->route('categories.create')
                        ->withErrors($validator)
                        ->withInput();
        }

        if ($request->hasFile('thumbnail')) {
            $fileNameWithExt = $request->file('thumbnail')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('thumbnail')->extension();
            $fileNameToStore = $fileName . '-' . time() . '.' . $extension;
            $path = $request->file('thumbnail')->storeAs(
                'public/thumbnails', $fileNameToStore
            );
        } else {
            $fileNameToStore = 'noimage.jpg';
        }

        $category = new Category;
        $category->name = $request->input('name');
        $category->slug = Str::of($request->input('name'))->slug('-');
        $category->parent_id = $request->input('parent_id');
        $category->description = $request->input('description');
        
        $category->save();

        return redirect()->route('categories.create')->with('success', 'New record created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $categories = Category::whereNull('parent_id')
                                ->with('categories')
                                ->get();
        return view('admin.category.edit', ['category' => $category, 'categories' => $categories]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route('categories.edit', $id)
                        ->withErrors($validator)
                        ->withInput();
        }

        if ($request->hasFile('thumbnail')) {
            $fileNameWithExt = $request->file('thumbnail')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('thumbnail')->extension();
            $fileNameToStore = $fileName . '-' . time() . '.' . $extension;
            $path = $request->file('thumbnail')->storeAs(
                'public/thumbnails', $fileNameToStore
            );
        }

        $category = Category::findOrFail($id);
        $category->name = $request->input('name');
        $category->slug = Str::of($request->input('name'))->slug('-');
        $category->parent_id = $request->input('parent_id');
        $category->description = $request->input('description');
        if ($request->hasFile('thumbnail')) {
            $category->thumbnail = $fileNameToStore;
        }

        $category->save();

        return redirect()->route('categories.edit', $id)->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Record deleted successfully.');
    }
}
