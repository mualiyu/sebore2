<?php

namespace App\Http\Controllers;

// use App\Http\Controllers\Controller;
use App\Http\Resources\ApiDeviceResource;
use App\Models\Api;
use App\Models\Category;
use App\Models\Device;
use App\Models\Item;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\HttpCache\ResponseCacheStrategy;

class ApiDeviceController extends Controller
{
    public function __construct()
    {
    }

    public function show(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'api_user' => 'required',
            'api_key' => 'required',
            'device_id' => 'required',
        ]);

        if ($validator->fails()) {
            $res = [
                'status' => false,
                'data' => $validator->errors(),
            ];
            return response()->json($res);
        }

        $api = Api::where('api_user', '=', $request->api_user)->get();
        if (count($api) > 0) {
            if ($api[0]->api_key == $request->api_key) {

                $device = Device::where('id', '=', $request->device_id)->with('org')->get();
                $device[0]->org->logo = url('/storage/pic') . '/' . $device[0]->org->logo;

                if (count($device) > 0) {
                    $org_cat = Category::where('org_id', '=', $device[0]->org_id)->get();

                    $category = [];

                    foreach ($org_cat as $oc) {
                        $category[$oc->name] = [];
                    }

                    $items = Item::where('device_id', '=', $device[0]->id)->get();

                    foreach ($items as $i) {
                        $i->item_cart->id = $i->id;
                        $item = $i->item_cart;
                        if ($item->with_q == 1) {
                            $item->with_q = true;
                        } else {
                            $item->with_q = false;
                        }
                        if ($item->with_p == 1) {
                            $item->with_p = true;
                        } else {
                            $item->with_p = false;
                        }

                        $cat = Category::find($item->category_id);

                        array_push($category[$cat->name], $item);
                    }

                    $data = [
                        'device' => $device[0],
                        'items' => $category
                    ];

                    $res = [
                        'status' => true,
                        'data' => $data
                    ];
                    return response()->json($res);
                } else {
                    $res = [
                        'status' => false,
                        'data' => 'No data found in system'
                    ];
                    return response()->json($res);
                }
                //
            } else {
                $res = [
                    'status' => false,
                    'data' => 'API_KEY Not correct'
                ];
                return response()->json($res);
            }
        } else {
            $res = [
                'status' => false,
                'data' => 'API_USER Not Found'
            ];
            return response()->json($res);
        }
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function update_code(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'api_user' => 'required',
            'api_key' => 'required',
            'device_id' => 'required',
            'device_code' => ['required', 'max:255'],
        ]);

        if ($validator->fails()) {
            $res = [
                'status' => false,
                'data' => $validator->errors(),
            ];
            return response()->json($res);
        }

        $api = Api::where('api_user', '=', $request->api_user)->get();
        if (count($api) > 0) {
            if ($api[0]->api_key == $request->api_key) {
                $d = Device::where('device_id', '=', $request->device_id)->get();

                $d_u = Device::where('device_id', '=', $request->device_id)->update([
                    'device_id' => $request->device_code
                ]);

                if ($d_u) {

                    $device = Device::where('id', '=', $d[0]->id)->with('org')->get();
                    $device[0]->org->logo = url('/storage/pic') . '/' . $device[0]->org->logo;

                    $org_cat = Category::where('org_id', '=', $device[0]->org_id)->get();

                    $category = [];

                    foreach ($org_cat as $oc) {
                        $category[$oc->name] = [];
                    }

                    $items = Item::where('device_id', '=', $device[0]->id)->get();

                    foreach ($items as $i) {

                        $i->item_cart->id = $i->id;
                        $item = $i->item_cart;

                        if ($item->with_q == 1) {
                            $item->with_q = true;
                        } else {
                            $item->with_q = false;
                        }
                        if ($item->with_p == 1) {
                            $item->with_p = true;
                        } else {
                            $item->with_p = false;
                        }

                        $cat = Category::find($item->category_id);

                        array_push($category[$cat->name], $item);
                    }

                    $data = [
                        'device' => $device[0],
                        'items' => $category
                    ];

                    $res = [
                        'status' => true,
                        'data' => $data
                    ];
                    return response()->json($res);
                } else {
                    $res = [
                        'status' => false,
                        'data' => 'Device not found in system'
                    ];
                    return response()->json($res);
                }
                //
            } else {
                $res = [
                    'status' => false,
                    'data' => 'API_KEY Not correct'
                ];
                return response()->json($res);
            }
        } else {
            $res = [
                'status' => false,
                'data' => 'API_USER Not Found'
            ];
            return response()->json($res);
        }
    }
}
