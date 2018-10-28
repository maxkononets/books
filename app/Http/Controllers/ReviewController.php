<?php

namespace App\Http\Controllers;

use App\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $book = Book::where('book_id', $request->book_id)->first();
        $user = Auth::user();
        if ($user->reviewedBooks()->save($book, ['discription' => $request->review])) {
            return response('review added', 200);
        }
        return response()->json([
            'error' => 'something went wrong',
        ], 200);
    }

    /**
     * @param $book_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function destroy($book_id)
    {
        $book = Book::where('book_id', $book_id)->first();
        $user = Auth::user();
        if ($user->reviewedBooks()->detach($book)) {
            return response('review deleted', 200);
        }
        return response()->json([
            'error' => 'something went wrong',
        ], 200);
    }

    /**
     * @param $book_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function allReview($book_id)
    {
        $book = Book::where('book_id', $book_id)->first();
        if ($reviews = $book->reviews) {
            $all = $reviews->map(function ($review) {
                return [
                    'discription' => $review->discription,
                    'user' => $review->owner->only(['name', 'id']),
                ];
            });
            return response()->json([
                'reviews' => $all,
            ], 200);
        }
        return response()->json('Book don`t have review', 200);
    }
}
