<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::where('is_active', true)
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->paginate(12);
            
        return view('client.posts.index', compact('posts'));
    }

    public function show($slug, $id)
    {
        $post = Post::where('is_active', true)
            ->whereNotNull('published_at')
            ->findOrFail($id);
            
        if ($post->slug !== $slug) {
            return redirect()->route('posts.show', ['slug' => $post->slug, 'id' => $post->id]);
        }

        $relatedPosts = Post::where('is_active', true)
            ->whereNotNull('published_at')
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('client.posts.show', compact('post', 'relatedPosts'));
    }
}
