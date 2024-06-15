<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = Category::all();

        return response()->json(CategoryResource::collection($categories));
    }
}
