<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;


class PostController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth')->except(["index", "show"]);
    }
    //
    public function index(User $user)
    {
        $posts  = Post::where('user_id',$user->id)->latest()->paginate(20);

        return view('dashboard', [
            'user' => $user,
            'posts' => $posts
        ]);
    }

    //Nos permite obtener el formulario
    public function create()
    {
        return view('posts.create');
    }

    //almacena en la base de datos
    public function store(Request $request)
    {
       $this->validate($request,[
        'titulo' => 'required|max:255',
        'descripcion' => 'required',
        'imagen'=>'required'
       ]);

       Post::create([
        'titulo' => $request->titulo,
        'descripcion' => $request->descripcion,
        'imagen' => $request->imagen,
        'user_id' => auth()->user()->id,
       ]);

       //otra forma

    //    $post = new Post;
    //    $post -> titulo = $request -> titulo;
    //    $post -> descripcion = $request -> descripcion;
    //    $post -> imagen = $request -> imagen;
    //    $post -> user_id = auth()->user()->id;
    //    $post -> save();
       
       //otra forma con relacion

    //    $request->user()->posts()->create([
    //     'titulo' => $request->titulo,
    //     'descripcion' => $request->descripcion,
    //     'imagen' => $request->imagen,
    //     'user_id' => auth()->user()->id,
    //    ]);


       return redirect()->route('posts.index', auth()->user()->username);
    }

    public function show(User $user, Post $post)
    {
        return view('posts.show',[
            'post' => $post,
            'user' => $user
        ]);
    }

    public function destroy(Post $post)
    {
        if($post->user_id === auth()->user()->id)
        {
            $this->authorize('delete', $post);
            $post->delete();

            //Eliminar Imagen
            $imagen_path = public_path('uploads/' . $post->imagen);

            if(File::exists($imagen_path))
            {
                unlink($imagen_path);
            }

            return redirect()->route('posts.index', auth()->user()->username);
        }
    }
}
