<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentRole;
use App\Models\Customer;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\PlanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AgentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function show_agents()
    {
        $agents = Agent::where('org_id', '=', Auth::user()->organization_id)->orderBy('created_at', 'desc')->get();

        return view('agents.index', compact('agents'));
    }

    public function show_single_agent($id)
    {
        $agent = Agent::find($id);

        return view('agents.agent_profile', compact('agent'));
    }

    public function show_add_agent()
    {
        return view('agents.add_agent');
    }

    public function create_agent(Request $request)
    {
        $org = Organization::find(Auth::user()->organization_id);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255', 'unique:agents'],
            'username' => ['required', 'string', 'max:255', 'unique:agents'],
            'address' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'integer', 'digits_between:4,4', 'confirmed'],
            'lga' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'gps' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $plan_detail = PlanDetail::where('org_id', '=', $org->id)->orderBy('id', 'desc')->first();

        if ($plan_detail && $plan_detail->status == 1) {

            $plan = Plan::find($plan_detail->plan_id);

            // return $plan;
            $agent = Agent::where('org_id', '=', $org->id)->get();

            if (count($agent) < $plan->no_agents) {

                $role = AgentRole::find($request['role']);

                $agent = Agent::create([
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'phone' => $request['phone'],
                    'password' => Hash::make($request['password']),
                    'username' => $request['username'],
                    'gps' => $request['gps'],
                    'state' => $request['state'],
                    'country' => $request['country'],
                    'address' => $request['address'],
                    'lga' => $request['lga'],
                ]);

                Agent::where('id', '=', $agent->id)->update([
                    'user_id' => Auth::user()->id,
                    'org_id' => Auth::user()->organization_id,
                    'agent_role_id' => $request['role'],
                ]);

                if ($agent) {
                    $customer = Customer::create([
                        'agent_id' => $agent->id,
                        'org_id' => Auth::user()->organization_id,
                        'name' => $agent->name,
                        'email' => $agent->email,
                        'phone' => $agent->phone,
                        'gps' => $agent->gps,
                        'state' => $agent->state,
                        'country' => $agent->country,
                        'address' => $agent->address,
                        'lga' => $agent->lga,
                    ]);

                    Customer::where('id', '=', $customer->id)->update([
                        // 'agent_id' => $agent->id,
                        'org_id' => Auth::user()->organization_id,
                    ]);
                    $agent_customer = DB::table('agent_customer')->insert([
                        'agent_id' => $agent->id,
                        'customer_id' => $customer->id
                    ]);
                    return redirect('/agents')->with(['success' => $agent->name . ' is Created to system as Agent and Customer']);
                } else {
                    return back()->with('error', "Sorry, Agent not created, Try again!");
                }
            } else {
                return back()->with('error', "Sorry, You have reached the maximum number of Agents allowed for your Plan. Upgrade to enjoy more of ATS services");
            }
        } else {
            return back()->with('error', "You don't have Any Active plan, Subscribe and try again.");
        }



        // //Api
        // $hash = hash(
        //     'sha512',
        //     $org->id .
        //         $request['name'] .
        //         $request['email'] .
        //         $request['password'] .
        //         $request['phone'] .
        //         $role->name
        // );
        // $url = 'https://api.ajisaqsolutions.com/api/agent/add?apiUser=' .
        //     config('app.apiUser') . '&apiKey=' .
        //     config('app.apiKey') . '&hash=' .
        //     $hash . '&id=' .
        //     $agent->phone . '&organizationId=' .
        //     $org->id . '&name=' .
        //     $request['name'] . '&email=' .
        //     $request['email'] . '&password=' .
        //     $request['password'] . '&phone=' .
        //     $request['phone'] . '&type=' .
        //     $role->name;


        // $response = Http::post($url);
        // // return $response;
        // $res = json_decode($response);

        // // dd($res);
        // if ($res->status != "Ok") {
        //     $agent->delete();
        //     return back()->with(['error' => 'Sorry, An error was encountered, Please try again later.'])->withInput();
        // }
        // //End Api


        // return redirect('/agents')->with(['success' => $agent->name . ' is Created to system as agent']);

        // dd($request->all());
    }

    public function update_agent(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'lga' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'gps' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return back()->with('error', 'Agent not Created. Try again!');
        }


        $agent = Agent::where('id', '=', $id)->update([
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'agent_role_id' => $request['role'],
            'username' => $request['username'],
            'gps' => $request['gps'],
            'state' => $request['state'],
            'country' => $request['country'],
            'address' => $request['address'],
            'lga' => $request['lga'],
        ]);


        return redirect()->route('show_single_agent', ['id' => $id])->with(['success' => $request['name'] . ' is Updated']);

        // dd($request->all());
    }
}
