<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Update Profil (Upload Foto ke MinIO)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'no_telp' => 'nullable|string|max:20',
            'foto_profil' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        // Handle Upload Foto MinIO
        if ($request->hasFile('foto_profil')) {
            // Hapus foto lama jika ada (dari MinIO)
            if ($user->foto_profil) {
                Storage::disk('public')->delete($user->foto_profil);
            }
            
            // Simpan foto baru ke MinIO
            $path = $request->file('foto_profil')->store('profil', 'public');
            $user->foto_profil = $path;
        }

        $user->name = $request->name;
        $user->no_telp = $request->no_telp;
        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data' => $user // JSON ini akan otomatis mengandung 'foto_profil_url' karena accessor di Model User
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password lama salah'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password berhasil diubah']);
    }
}