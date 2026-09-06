<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function shipping()
    {
        return view('client.pages.shipping');
    }

    public function returnPolicy()
    {
        return view('client.pages.return_policy');
    }

    public function howToBuy()
    {
        return view('client.pages.how_to_buy');
    }

    public function privacy()
    {
        return view('client.pages.privacy');
    }

    public function contact()
    {
        return view('client.pages.contact');
    }

    public function brandStory()
    {
        return view('client.pages.brand_story');
    }

    public function stores()
    {
        return view('client.pages.stores');
    }

    public function careers()
    {
        return view('client.pages.careers');
    }

    public function press()
    {
        return view('client.pages.press');
    }

    public function loyalty()
    {
        return view('client.pages.loyalty');
    }
}
