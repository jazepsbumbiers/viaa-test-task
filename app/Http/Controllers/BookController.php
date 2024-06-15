<?php

namespace App\Http\Controllers;

use App\Http\Requests\Book\StoreRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * @param StoreRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $book = Book::create([
            'title'             => $request->title,
            'type'              => $request->type,
            'category_id'       => $request->category,
            'manufacturer_id'   => $request->manufacturer,
            'summary'           => $request->summary,
            'price'             => $request->price,
            'in_stock'          => $request->in_stock,
        ]);

        foreach ($request->photos as $photoData) {
            $photoFileName = explode('/', $photoData['photoPath']);

            $book->photo()->create([
                'name'      => $photoData['name'],
                'caption'   => $photoData['caption'] ?? null,
                'size'      => Storage::disk('public')->size("uploads/images/$photoFileName[2]"),
                'path'      => "uploads/images/$photoFileName[2]",
                'book_id'   => $book->id,
            ]);
        }

        $book->load(['photo']);

        return response()->json(new BookResource($book));
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
