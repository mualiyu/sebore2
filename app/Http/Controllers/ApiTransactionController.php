<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Api;
use App\Models\Customer;
use App\Models\Device;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiTransactionController extends Controller
{

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'api_user' => 'required',
            'api_key' => 'required',
            'agent_id' => 'required',
            'ref_id' => 'required',
            'device_id' => 'required',
            'item_id' => 'required',
            'customer_phone' => 'required',
            'quantity' => 'required',
            'date' => 'required',
            'amount' => 'required',
            'type' => 'nullable'
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

                $device = Device::find($request->device_id);

                $customer = Customer::where('phone', '=', $request->customer_phone)->get();

                $agent = Agent::find($request->agent_id);

                $trans = Transaction::where('ref_id', '=', $request->ref_id)->get();

                if (count($trans) > 0) {
                    $res = [
                        'status' => false,
                        'data' => 'RECORD_EXIST',
                    ];
                    return response()->json($res);
                } else {

                    if (count($customer) > 0) {

                        if ($request->type == "sale") {
                            // sale detail
                            $sale = Sale::where(['item_id' => $request->item_id, 'marketer_id' => $agent->id])->get();
                            $sale_amount = $sale[0]->amount;
                            $sale_quantity = $sale[0]->quantity;

                            // new sale detail
                            $sale_amount -= $request->amount;
                            $sale_quantity -= $request->quantity;


                            // // store detail
                            // $store = Store::find($sale[0]->store_id);
                            // $store_amount = $store->total_amount;
                            // $store_items = $store->total_num_of_items;

                            // // new store data
                            // $store_amount -= $request->amount;
                            // $store_items -= $request->quantity;

                            // agent wallet 
                            $wallet = $agent->wallet;
                            // new wallet
                            $wallet -= $request->amount;

                            // create transaction for sale
                            $transaction = Transaction::create([
                                'org_id' => $device->org_id,
                                'agent_id' => $request->agent_id,
                                'device_id' => $request->device_id,
                                'item_id' => $request->item_id,
                                'customer_id' => $customer[0]->id,
                                'quantity' => $request->quantity,
                                'date' => $request->date,
                                'amount' => $request->amount,
                                'ref_id' => $request->ref_id,
                                'p_status' => 0,
                                'type' => "sale",
                            ]);
                            Transaction::where('id', '=', $transaction->id)->update([
                                'ref_id' => $request->ref_id,
                                'p_status' => 0,
                                'type' => "sale",
                            ]);

                            if ($transaction) {
                                $update_sale = Sale::where("id", "=", $sale[0]->id)->update([
                                    "amount" => $sale_amount,
                                    "quantity" => $sale_quantity,
                                ]);
                                // $update_store = Store::where('id', '=', $store->id)->update([
                                //     'total_amount' => $store_amount,
                                //     "total_num_of_items" => $store_items,
                                // ]);
                                $update_agent_wallet = Agent::where('id', '=', $agent->id)->update([
                                    'wallet' => $wallet,
                                ]);
                                $res = [
                                    'status' => true,
                                    'data' => $transaction
                                ];
                                return response()->json($res);
                                // return $transaction;
                            } else {
                                $res = [
                                    'status' => false,
                                    'data' => 'Fail to store transaction'
                                ];
                                return response()->json($res);
                            }
                        } else {

                            // create transaction for collection
                            $transaction = Transaction::create([
                                'org_id' => $device->org_id,
                                'agent_id' => $request->agent_id,
                                'device_id' => $request->device_id,
                                'item_id' => $request->item_id,
                                'customer_id' => $customer[0]->id,
                                'quantity' => $request->quantity,
                                'date' => $request->date,
                                'amount' => $request->amount,
                                'ref_id' => $request->ref_id,
                                'p_status' => 0,
                                'type' => "collection"
                            ]);
                            Transaction::where('id', '=', $transaction->id)->update([
                                'ref_id' => $request->ref_id,
                                'p_status' => 0,
                            ]);

                            if ($transaction) {
                                $res = [
                                    'status' => true,
                                    'data' => $transaction
                                ];
                                return response()->json($res);
                            } else {
                                $res = [
                                    'status' => false,
                                    'data' => 'Fail to store transaction'
                                ];
                                return response()->json($res);
                            }
                        }
                    } else {
                        $res = [
                            'status' => false,
                            'data' => 'CUSTOMER_NOT_EXIST(' . $request->customer_phone . ')'
                        ];
                        return response()->json($res);
                    }
                }
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
