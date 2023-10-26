<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        if ($currentUser && $currentUser->roles === 'admin') {
            if (request()->ajax()) {
                $query = User::latest()->get();

                return Datatables::of($query)
                    ->addColumn('action', function ($item) {
                        return '
                            <a class="btn btn-primary btn-xs" href="' . route('user.edit', $item->id) . '">
                                <i class="fas fa-edit"></i> &nbsp; Ubah
                            </a>
                            <form action="' . route('user.destroy', $item->id) . '" method="POST" onsubmit="return confirm(' . "'Anda akan menghapus item ini secara permanen dari situs anda?'" . ')">
                                ' . method_field('delete') . csrf_field() . '
                                <button class="btn btn-danger btn-xs">
                                    <i class="far fa-trash-alt"></i> &nbsp; Hapus
                                </button>
                            </form>
                        ';
                    })
                    ->editColumn('name', function ($item) {
                        return $item->profile ?
                            '<div class="d-flex align-items-center">
                                        <div class="avatar me-2"><img class="avatar-img img-fluid" src="' . Storage::url($item->profile) . '" /></div>' .
                            $item->name . '
                                    </div>'
                            :
                            '<div class="d-flex align-items-center">
                                        <div class="avatar me-2"><img class="avatar-img img-fluid" src="https://ui-avatars.com/api/?name=' . $item->name . '" /></div>' .
                            $item->name . '
                                    </div>';
                    })
                    ->addIndexColumn()
                    ->removeColumn('id')
                    ->rawColumns(['action', 'name'])
                    ->make();
            }

            return view('pages.admin.user.index');
        }
        return redirect()->route('admin-dashboard')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman Data User');

    }

    public function create()
    {
        $currentUser = auth()->user();
        if ($currentUser && $currentUser->roles === 'admin') {
            return view('pages.admin.user.create');
        }
        return redirect()->route('user.index')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email:dns|unique:users',
            'password' => 'required|min:5|max:255',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        User::create($validatedData);

        return redirect()
            ->route('user.index')
            ->with('success', 'Sukses! Data Pengguna Berhasil Disimpan');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();

        return view('pages.admin.user.index', [
            'user' => $user
        ]);
    }

    public function edit($id)
    {
        $item = User::findOrFail($id);

        return view('pages.admin.user.edit', [
            'item' => $item
        ]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email:dns',
        ]);

        $item = User::findOrFail($id);

        $item->update($validatedData);

        return redirect()
            ->route('user.index')
            ->with('success', 'Sukses! Data Pengguna telah diperbarui');
    }

    public function destroy($id)
    {
        $item = User::findorFail($id);

        Storage::delete($item->profile);

        $item->delete();

        return redirect()
            ->route('user.index')
            ->with('success', 'Sukses! Data Pengguna telah dihapus');
    }

    // public function upload_profile(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'profile' => 'required|image|file|max:1024',
    //     ]);

    //     $id = $request->id;
    //     $item = User::findOrFail($id);

    //     //dd($item);

    //     if ($request->file('profile')) {
    //         Storage::delete($item->profile);
    //         $item->profile = $request->file('profile')->store('assets/profile-images');
    //     }

    //     $item->save();

    //     return redirect()
    //         ->route('user.index')
    //         ->with('success', 'Sukses! Photo Pengguna telah diperbarui');
    // }

    public function upload_profile(Request $request)
    {
        $validatedData = $request->validate([
            'profile' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        $id = $request->id;
        $item = User::find($id);

        if (!$item) {
            return redirect()
                ->route('user.index')
                ->with('error', 'Pengguna tidak ditemukan');
        }

        if ($request->file('profile')->isValid()) {
            if ($item->profile) {
                Storage::delete($item->profile);
            }
            $item->profile = $request->file('profile')->store('assets/profile-images');
            $item->save();
            return redirect()
                ->route('user.index')
                ->with('success', 'Sukses! Foto Pengguna telah diperbarui');
        } else {
            return redirect()
                ->route('user.index')
                ->with('error', 'File profil tidak valid');
        }
    }


    public function change_password()
    {
        return view('pages.admin.user.change-password');
    }


}