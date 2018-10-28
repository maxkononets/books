<?php

namespace App\Http\Controllers;

use App\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishListController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($book = Book::where('book_id', $request->book_id)->first()) {
            if ($book->bookWishes()->where('user_id', Auth::id())->first()) {
                return response()->json([
                    'error' => 'Book has been added!',
                ]);
            }
            Auth::user()->wishes()->attach($book);
            return response('OK', 200);
        }

        $book = new Book();
        $book->fill($request->all())->save();
        Auth::user()->wishes()->attach($book);
        return response('OK', 200);
    }

    /**
     * @param $book_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function destroy($book_id)
    {
        $book = Book::where('book_id', $book_id)->first();
        if (Auth::user()->wishes()->detach($book)) {
            return response('OK', 200);
        }
        return response()->json([
            'errors' => 'Something went wrong.'
        ], 200);
    }
}
