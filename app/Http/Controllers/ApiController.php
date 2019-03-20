<?php

namespace App\Http\Controllers;

use App\LiveImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function liveImageList()
    {
        try {
            $liveImages = LiveImage::all();
            return response()->json(['success' => true, 'status' => 200, 'images' => $liveImages]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'status' => get_class($e), 'message' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function liveImageStore(Request $request)
    {
        try {
            if ($request->imageBlob) {
                $image = base64_decode($request->imageBlob);
                $imageName = Str::random(20);
                $fileUploaded = Storage::disk('s3')->put('/source/' . $imageName . '.jpg', $image, 'public');
                if ($fileUploaded) {
                    return response()->json(['success' => true, 'status' => 200, 'imageName' => $imageName]);
                }
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'status' => get_class($e), 'message' => $e->getMessage()]);
        }
    }

    public function liveImageUpdateWithCount(Request $request, $imageName)
    {
        try {
            if ($request->imageName && $request->numPeopleDetected) {
                $liveImage = LiveImage::create(['imageName' => $imageName, 'numPeopleDetected' => $request->numPeopleDetected]);
                return response()->json(['success' => true, 'status' => 200]);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'status' => get_class($e), 'message' => $e->getMessage()]);
        }
    }
}
