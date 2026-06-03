<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Hash, Validator};

class ProfileController extends Controller
{
    // GET /api/profile
    public function show(Request $request): JsonResponse
    {
        $u = $request->user();
        return response()->json(['success' => true, 'data' => [
            'id'    => $u->id,
            'name'  => $u->name,
            'email' => $u->email,
            'phone' => $u->phone,
            'role'  => $u->role,
            'photo' => $u->photo,
        ]]);
    }

    // PUT /api/profile — update own name + phone
    public function update(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name'  => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
        ]);
        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()], 422);

        $u = $request->user();
        $u->name  = trim($request->name);
        $u->phone = $request->phone ?: null;
        $u->save();

        return response()->json(['success'=>true,'message'=>'Profile updated','data'=>[
            'name'=>$u->name,'phone'=>$u->phone,
        ]]);
    }

    // POST /api/profile/photo — store avatar as a data-URL (base64)
    public function photo(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['photo' => 'required|string']);
        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()], 422);

        $photo = $request->photo;
        // Only accept image data URLs, cap at ~2.7MB of base64 (~2MB image)
        if (! preg_match('/^data:image\/(png|jpe?g|gif|webp);base64,/', $photo)) {
            return response()->json(['success'=>false,'message'=>'Invalid image format'], 422);
        }
        if (strlen($photo) > 2_800_000) {
            return response()->json(['success'=>false,'message'=>'Image too large (max ~2MB)'], 422);
        }

        $u = $request->user();
        $u->photo = $photo;
        $u->save();

        return response()->json(['success'=>true,'message'=>'Photo updated','photo'=>$photo]);
    }

    // DELETE /api/profile/photo
    public function removePhoto(Request $request): JsonResponse
    {
        $u = $request->user();
        $u->photo = null;
        $u->save();
        return response()->json(['success'=>true,'message'=>'Photo removed']);
    }

    // PUT /api/profile/password — change own password
    public function password(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);
        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()], 422);

        $u = $request->user();
        if (! Hash::check($request->current_password, $u->password)) {
            return response()->json(['success'=>false,'message'=>'Current password is incorrect'], 422);
        }
        $u->password = Hash::make($request->password);
        $u->save();

        return response()->json(['success'=>true,'message'=>'Password changed']);
    }
}
