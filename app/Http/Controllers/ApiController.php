<?php

namespace App\Http\Controllers;

use App\LiveImage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $request->order = isset($request->order) ? $request->order : 'asc';
            // Check for query parameters
            $rules = array(
                'limit' => ['integer', 'min:1'],
                'order' => ['nullable', Rule::in(['asc', 'desc'])],
                'from' => ['nullable', 'date', 'required_with:to'],
                'to' => ['nullable', 'date', 'after:from', 'required_with:from'],
                'aggregate' => ['nullable', Rule::in(['week', 'month'])],
            );

            // Validate query parameters
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'status' => 400, 'message' => $validator->errors()->all()]);
            }

            // Start sorting based on queries (if any)
            if ($request->from && $request->to) {
                $liveImages = LiveImage::orderBy('created_at', $request->order)->limit($request->limit)->whereBetween('created_at', [$request->from, $request->to])->get();
            } else {
                $liveImages = LiveImage::orderBy('created_at', $request->order)->limit($request->limit)->get();
            }

            // Check if aggregate exists
            if ($request->aggregate) {
                if ($request->aggregate == 'week') {
                    $liveImages = $liveImages->groupBy(function ($liveImage) {
                        return Carbon::parse($liveImage->created_at)->format('W-Y');
                    });
                } else {
                    $liveImages = $liveImages->groupBy(function ($liveImage) {
                        return Carbon::parse($liveImage->created_at)->format('M-Y');
                    });
                }
            }

            return response()->json(['success' => true, 'status' => 200, 'images' => $liveImages]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'status' => get_class($e), 'message' => $e->getMessage()]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function average(Request $request)
    {
        try {
            $request->order = isset($request->order) ? $request->order : 'asc';
            // Check for query parameters
            $rules = array(
                'from' => ['required', 'date'],
                'to' => ['required', 'date', 'after:from'],
            );

            // Validate query parameters
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'status' => 400, 'message' => $validator->errors()->all()]);
            }

            // Start sorting based on queries (if any)
            if ($request->from && $request->to) {
                $liveImages = round(floatval(LiveImage::orderBy('created_at', $request->order)->limit($request->limit)->whereBetween('created_at', [$request->from, $request->to])->avg('numPeopleDetected')), 2);
            }

            return response()->json(['success' => true, 'status' => 200, 'average' => $liveImages]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'status' => get_class($e), 'message' => $e->getMessage()]);
        }
    }

    public function paginate(Request $request)
    {
        try {
            $request->order = isset($request->order) ? $request->order : 'asc';

            // Check for query parameters
            $rules = array(
                'index' => ['required', 'integer', 'min:0'],
                'limit' => ['required', 'integer', 'min:1'],
                'order' => ['nullable', Rule::in(['asc', 'desc'])],
            );

            // Validate query parameters
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'status' => 400, 'message' => $validator->errors()->all()]);
            }

            $liveImages = LiveImage::orderBy('created_at', $request->order)->limit($request->limit)->offset($request->index)->get();

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
            if ($request->imageName) {
                if ($request->numPeopleDetected) {
                    $liveImage = LiveImage::create(['imageName' => $imageName, 'numPeopleDetected' => $request->numPeopleDetected]);
                } else {
                    $liveImage = LiveImage::create(['imageName' => $imageName, 'numPeopleDetected' => 0]);
                }
                return response()->json(['success' => true, 'status' => 200]);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'status' => get_class($e), 'message' => $e->getMessage()]);
        }
    }
}
