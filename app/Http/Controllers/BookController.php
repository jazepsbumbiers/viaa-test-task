<?php

namespace App\Http\Controllers;

use App\Http\Requests\Book\StoreRequest;
use App\Http\Requests\Book\UpdateRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    /**
     * @param Request $request
     *
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('perPage');
        $page = (int) $request->query('page');

        $items = Book::with('category', 'manufacturer', 'photo')->get();

        $currentItems = $items->slice(($page - 1) * $perPage, $perPage);
        $paginator = new LengthAwarePaginator($currentItems, count($items), $perPage, $page);

        $bookResourceCollection = BookResource::collection(
            new Collection($paginator->items())
        );

        $bookResourceCollection->additional([
            'meta' => [
                'total'         => $paginator->total(),
                'per_page'      => $paginator->perPage(),
                'current_page'  => $paginator->currentPage(),
                'last_page'     => $paginator->lastPage(),
                'from'          => $paginator->firstItem(),
                'to'            => $paginator->lastItem(),
            ],
        ]);

        return $bookResourceCollection;
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

        if (count($request->photos) === 0) {
            $book->photo()->create([
                'name'      => 'placeholder.png',
                'caption'   =>  null,
                'size'      => Storage::disk('public')->size("uploads/images/placeholder.png"),
                'path'      => "uploads/images/placeholder.png",
                'book_id'   => $book->id,
            ]);
        }

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

        return response()->json(new BookResource($book));
    }

    /**
     * @param Book $book
     *
     * @return JsonResponse
     */
    public function show(Book $book): JsonResponse
    {
        $book->load(['manufacturer', 'category', 'photo']);

        return response()->json(new BookResource($book));
    }

    /**
     * @param UpdateRequest $request
     * @param Book $book
     *
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, Book $book): JsonResponse
    {
        $book->load(['photo']);

        $book->update([
            'title'             => $request->title,
            'type'              => $request->type,
            'category_id'       => $request->category,
            'manufacturer_id'   => $request->manufacturer,
            'summary'           => $request->summary,
            'price'             => $request->price,
            'in_stock'          => $request->in_stock,
        ]);

        $book->photo()->delete();

        if (count($request->photos) === 0) {
            $book->photo()->create([
                'name'      => 'placeholder.png',
                'caption'   =>  null,
                'size'      => Storage::disk('public')->size("uploads/images/placeholder.png"),
                'path'      => "uploads/images/placeholder.png",
                'book_id'   => $book->id,
            ]);
        }

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

        $book->save();

        return response()->json(new BookResource($book));
    }

    /**
     * @param Book $book
     *
     * @return JsonResponse
     */
    public function destroy(Book $book): JsonResponse
    {
        $book->delete();

        return response()->json();
    }
}
