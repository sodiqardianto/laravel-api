<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(5);
        return new PostResource(true, 'List Data Post', $posts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpg,png,jpeg',
            'title'     => 'required',
            'content'   => 'required'
        ]);

        if ($validator->fails()) {
            return new PostResource(false, 'Error Validasi', $validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        $post = Post::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'content'   => $request->content
        ]);

        if ($post) {
            return new PostResource(true, 'Data Post Berhasil Disimpan', $post);
        } else {
            return new PostResource(false, 'Data Post Gagal Disimpan', null);
        }
    }

    public function show($id)
    {
        $post = Post::findOrFail($id);
        return new PostResource(true, 'Detail Data Post', $post);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image'     => 'image|mimes:jpg,png,jpeg',
            'title'     => 'required',
            'content'   => 'required'
        ]);

        if ($validator->fails()) {
            return new PostResource(false, 'Error Validasi', $validator->errors(), 422);
        }

        $post = Post::findOrFail($id);

        if ($request->file('image')) {
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            Storage::delete('public/posts/' . $post->image);

            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content
            ]);
        } else {
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content
            ]);
        }

        if ($post) {
            return new PostResource(true, 'Data Post Berhasil Diupdate', $post);
        } else {
            return new PostResource(false, 'Data Post Gagal Diupdate', null);
        }
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        Storage::delete('public/posts/' . basename($post->image));
        $post->delete();

        if ($post) {
            return new PostResource(true, 'Data Post Berhasil Dihapus', $post);
        } else {
            return new PostResource(false, 'Data Post Gagal Dihapus', null);
        }
    }
}
