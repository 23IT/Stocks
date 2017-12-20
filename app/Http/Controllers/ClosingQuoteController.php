<?php

namespace App\Http\Controllers;

use App\ClosingQuote;
use Illuminate\Http\Request;

class ClosingQuoteController extends Controller
{

    protected $limit = 50;

    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index($page = 1)
    {
        $sword = \request()->has('sword') ? \request()->get('sword') : false;

        $query = ClosingQuote::offset(($page-1) * $this->limit)
            ->limit($this->limit)
            ->orderBy('date_quote', 'desc');

        if ($sword) {
            $query->where('symbol', 'like', "%$sword%");
        }

        return [
            'results' => $query
                ->get()
                ->toArray(),
            'error' => \request()->toArray(),
            'page' => $page
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  string $symbol
     * @return array
     */
    public function show($symbol, $page = 1)
    {
        return ClosingQuote::offset(($page-1) * $this->limit)
            ->where('symbol', $symbol)
            ->orderBy('date_quote', 'desc')
            ->limit($this->limit)
            ->get()
            ->toArray();
    }
}
