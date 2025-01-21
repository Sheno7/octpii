<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\AddCustomer;
use App\Models\AdditionalInformation;
use App\Models\AdditionalInformationMeta;
use App\Models\Booking;
use App\Models\Customers;
use App\Models\Setting;
use App\Models\User;
use App\Models\VeServices;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Validation\Rule;

class VeCustomersController extends Controller {
    use ResponseTrait;

    public function index(Request $request) {
        try {
            // Initialize query builder
            $query = Customers::select(
                'customers.id',
                'users.id as user_id',
                'users.first_name',
                'users.last_name',
                'customers.rating',
                'customers.created_at',
                'users.phone',
                'countries.code'
            )
                ->join('users', 'customers.user_id', '=', 'users.id')
                ->join('countries', 'users.country_id', '=', 'countries.id')
                ->orderBy('customers.id', 'desc');

            if ($request->has('phone')) {
                $phone = $request->input('phone');
                $query->where('users.phone', 'like', "%$phone%");
            }

            if ($request->has('rating')) {
                $rating = $request->input('rating');
                $query->where('customers.rating', '<=', $rating);
            }

            if ($request->has('name')) {
                $query->where('users.first_name', 'like', '%' . $request->input('name') . '%')
                    ->orWhereRaw('LOWER(users.first_name) LIKE ?', ['%' . strtolower($request->input('name')) . '%'])
                    ->orWhereRaw('LOWER(users.last_name) LIKE ?', ['%' . strtolower($request->input('name')) . '%']);
            }

            if ($request->has('created_at_start')) {
                $startDate = $request->input('created_at_start');

                if ($request->has('created_at_end')) {
                    $endDate = $request->input('created_at_end');
                    $query->whereBetween(DB::raw('DATE(customers.created_at)'), [$startDate, $endDate]);
                } else {
                    $query->whereDate('customers.created_at', '>=', $startDate);
                }
            }


            $data = $query->paginate(10);
            foreach ($data as $customer) {
                $transaction = new VeTransactionsController();
                $customer->total_spent = $transaction->getCustomerTotalSpent($customer->id);
                $customer->total_booking = Booking::where('customer_id', $customer->id)->count();
                // customer outstanding balance , call getCustomerOutstanding($customer_id) in veTransactionController

                $customer->outstanding = $transaction->getCustomerOutstanding($customer->id)['outstanding'];
            }
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function add(AddCustomer $request) {
        try {
            DB::beginTransaction();
            // add user before add customer
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'name' => $request->first_name . ' ' . $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'country_id' => $request->country_id,
                'gender' => $request->gender,
                'dob' => $request->dob,
                'referral' => $request->referral,
                'password' => bcrypt(random_int(1, 1000)),
            ]);
            $user->assignRole('customer');
            // add customer
            $customer_id = DB::table('customers')->insertGetId([
                'user_id' => $user->id,
                'rating' => 0,
                'status' => 1,
                'branch_id' => $request->get('branch_id', $request->get('selected_branch', null)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // get id from customer
            $customerId = DB::table('customers')->where('user_id', $user->id)->first()->id;
            // count service location if count > 0 add address should be empty

            if (!empty($request->area_id)) {
                DB::table('address')->insert([
                    'owner_id' => $customerId,
                    'owner_type' => 1,
                    'area_id' => $request->area_id,
                    'location_name' => $request->location_name,
                    'unit_type' => $request->unit_type ?? 2,
                    'unit_size' => $request->unit_size ?? 0,
                    'street_name' => $request->street_name ?? '',
                    'building_number' => $request->building_number ?? '',
                    'floor_number' => $request->floor_number,
                    'unit_number' => $request->unit_number,
                    'notes' => $request->notes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (!empty($request->additional_info)) { {
                    $setting = Setting::where('key', 'customer_add')->value('value');
                    foreach ($setting as $item) {
                        $tmp = collect($request->additional_info)->where('type', $item['type'])->first();
                        if (empty($tmp)) {
                            $tmp = $item;
                        }
                        $additionalInfo = new AdditionalInformation();
                        $additionalInfo->customer_id = $customerId;
                        $additionalInfo->type = $item['type'];
                        $additionalInfo->hasfile = $item['hasfile'];
                        $additionalInfo->value = $tmp;
                        $additionalInfo->save();
                    }
                }
            }
            DB::commit();
            return $this->getSuccessResponse('success',  ['id' => $customer_id]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function edit(Request $request) {
        try {
            $userId = Customers::where('id', $request->id)->first()->user_id;
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:customers,id'],
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['nullable', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:255', 'unique:users,phone,' . $userId],
                'email' => ['nullable', 'email', 'max:50', 'unique:users,email,' . $userId],
                'country_id' => ['required', 'integer', 'exists:countries,id'],
                'gender' => ['required', 'integer']
            ]);
            if ($validator->fails()) {
                return $this->getErrorResponse('error', $validator->errors(), 422);
            }
            // check if user exists
            DB::beginTransaction();
            $user = DB::table('users')->where('phone', $request->phone)->first();
            if (!$user) {
                return $this->getErrorResponse('error', 'User not found', 422);
            }
            // update user
            DB::table('users')->where('phone', $request->phone)->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'name' => $request->first_name . ' ' . $request->last_name,
                'phone' => $request->phone ?? $user->phone,
                'email' => $request->email,
                'country_id' => $request->country_id,
                'gender' => $request->gender,
                'dob' => $request->dob,
                'referral' => $request->referral,
                'updated_at' => now(),
            ]);
            // update user
            DB::table('customers')->where('id', $request->customer_id)->update([
                'rating' => $request->rating,
                'status' => $request->status,
                'updated_at' => now(),
            ]);
            if ($request->has('additional_info') && is_array($request->additional_info)) {
                foreach ($request->additional_info as $item) {
                    $existingData = AdditionalInformation::where('customer_id', $request->id)
                        ->where('type', $item['type'])->first();

                    if (empty($existingData)) {
                        $existingData = new AdditionalInformation([
                            'customer_id' => $request->id,
                            'type' => $item['type'],
                        ]);
                    }

                    $existingData->value = $item;
                    $existingData->save();
                }
            }
            DB::commit();
            return $this->getSuccessResponse('success');
        } catch (\Throwable $th) {
            DB::rollBack();
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function show(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:customers,id'],
            ]);
            if ($validator->fails()) {
                return $this->getValidationErrorResponse('error', $validator->errors());
            }
            $data = DB::table('customers')
                ->select(
                    'customers.id',
                    'users.id as user_id',
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                    'users.phone',
                    'users.country_id',
                    'users.gender',
                    'users.dob',
                    'users.referral',
                    'users.image',
                    'countries.code as country_code',
                    'customers.rating',
                    'customers.created_at'
                )
                ->join('users', 'customers.user_id', '=', 'users.id')
                ->join('countries', 'countries.id', '=', 'users.country_id')
                ->where('customers.id', $request->id)
                ->first();
            $data->additional_info = $this->combined_data($request->id);
            //$data->total_spent = Booking::where('customer_id', $request->id)->where('booking.status', 2)->sum('total');
            $transaction = new VeTransactionsController();
            $data->total_spent = $transaction->getCustomerTotalSpent($request->id);
            $data->total_booking = Booking::where('customer_id', $request->id)->count();
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    protected function combined_data($id) {
        $additionalInfo = $this->additional_information($id);
        $check = AdditionalInformation::where('customer_id', $id)->where('hasfile', true)->count();
        if ($check > 0) {
            $media = [$this->additional_information_media($id)];
            return array_merge($additionalInfo, $media);
        }
        return $additionalInfo;
    }

    protected function additional_information($id) {
        try {
            return AdditionalInformation::where('customer_id', $id)
                ->where(function ($query) {
                    $query->where('hasfile', 0)
                        ->orWhere('type', 'dental_chart');
                })
                ->pluck('value')
                ->toArray();
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    protected function additional_information_media($id) {
        try {
            $media = (new VeMediaController())->getMediaByModelId('xrayimage', $id);
            $combinedData = [
                "type" => "XRAy",
                "hasfile" => true,
                "value" => [
                    "sections" => []
                ]
            ];
            foreach ($media as $item) {
                $section = [
                    "id" => $item['id'],
                    "title" => $item['title'],
                    "type" => "images",
                    "value" => url('uploads/media/xrayimage/' . $item['file'])
                ];
                $combinedData["value"]["sections"][] = $section;
            }
            return $combinedData;
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    // add new address to customer
    public function addAddress(Request $request) {
        try {
            // validate id
            $validator = Validator::make($request->all(), [
                'customer_id' => ['required', 'integer', 'exists:customers,id'],
                // 'owner_type' => ['required', 'integer',1],
                'area_id' => ['required', 'integer', 'exists:areas,id'],
                'location_name' => ['required', 'string'],
                'unit_type' => ['required', 'integer'],
                'unit_size' => ['required', 'integer'],
                'street_name' => ['required', 'string'],
                'building_number' => ['required', 'string']
            ]);
            if ($validator->fails()) {
                return $this->getValidationErrorResponse('error', $validator->errors());
            }
            DB::table('address')
                ->insert([
                    'owner_id' => $request->customer_id,
                    'owner_type' => 1,
                    'area_id' => $request->area_id,
                    'location_name' => $request->location_name,
                    'unit_type' => $request->unit_type,
                    'unit_size' => $request->unit_size,
                    'street_name' => $request->street_name,
                    'building_number' => $request->building_number,
                    'floor_number' => $request->floor_number,
                    'unit_number' => $request->unit_number
                ]);
            return $this->getSuccessResponse('success');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    // get customer list of address

    public function listAddress(Request $request) {
        try {
            // validate id
            $validator = Validator::make($request->all(), [
                'owner_id' => ['required', 'integer', 'exists:customers,id'],
            ]);
            if ($validator->fails()) {
                return $this->getValidationErrorResponse('error', $validator->errors());
            }
            $data = DB::table('address')
                ->select('areas.id', 'areas.title_en as area_title', 'cities.id as city_id', 'cities.title_en as city_title', 'countries.title_en as country_title', 'countries.id as countries.id', 'address.id', 'address.owner_id', 'address.owner_type', 'address.area_id', 'address.location_name', 'address.unit_type', 'address.unit_size', 'address.street_name', 'address.building_number', 'address.floor_number', 'address.unit_number')
                ->join('customers', 'address.owner_id', '=', 'customers.id')
                ->join('areas', 'address.area_id', '=', 'areas.id')
                ->leftJoin('cities', 'areas.city_id', '=', 'cities.id')
                ->leftJoin('countries', 'cities.country_id', '=', 'countries.id')
                ->where('address.owner_id', $request->owner_id)
                ->where('address.owner_type', 1)
                ->orderBy('address.id', 'desc')
                ->paginate(10);
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function editAddress(Request $request) {
        try {
            // validate id
            $validator = Validator::make($request->all(), [
                'address_id' => ['required', 'integer', 'exists:address,id'],
                'customer_id' => ['required', 'integer', 'exists:customers,id'],
                //  'owner_type' => ['required', 'integer', 1],
                'area_id' => ['required', 'integer', 'exists:areas,id'],
                'location_name' => ['required', 'string'],
                'unit_type' => ['required', 'integer'],
                'unit_size' => ['required', 'integer'],
                'street_name' => ['required', 'string'],
                'building_number' => ['required', 'string']
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            DB::table('address')
                ->where('id', $request->address_id)
                ->update([
                    'owner_id' => $request->customer_id,
                    'owner_type' => 1,
                    'area_id' => $request->area_id,
                    'location_name' => $request->location_name,
                    'unit_type' => $request->unit_type,
                    'unit_size' => $request->unit_size,
                    'street_name' => $request->street_name,
                    'building_number' => $request->building_number,
                    'address.floor_number' => $request->floor_number,
                    'address.unit_number' => $request->unit_number
                ]);
            return $this->getSuccessResponse('success');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function deleteAddress(Request $request) {
        try {
            // validate id
            $validator = Validator::make($request->all(), [
                'address_id' => ['required', 'integer', 'exists:address,id'],
                'customer_id' => ['required', 'integer', 'exists:customers,id']
            ]);
            if ($validator->fails()) {
                return $this->getValidationErrorResponse('error', $validator->errors());
            }
            DB::table('address')
                ->where('id', $request->address_id)
                ->where('owner_id', $request->customer_id)
                ->where('owner_type', 1)->delete();
            return $this->getSuccessResponse('success');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function dropdown(Request $request) {
        try {
            $data = DB::table('customers')
                ->join('users', 'users.id', 'customers.user_id')
                ->select('customers.id', 'users.first_name', 'users.last_name', 'users.phone')
                ->where(DB::raw('LOWER(users.first_name)'), 'LIKE', '%' . strtolower($request->search) . '%')
                ->orWhereRaw('LOWER(users.last_name) LIKE ?', ['%' . strtolower($request->search) . '%'])
                ->orWhereRaw('users.phone LIKE ?', ['%' . $request->search . '%'])
                ->orderBy('customers.id', 'desc')
                ->paginate(10);
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function uploadXray(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required',
                'title' => 'required|string|max:50',
                'customer_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return $this->getErrorResponse($validator->errors()->first());
            }
            $media = new VeMediaController();
            $model_type = 'xrayimage';
            $file = $request->file('file');
            $title = $request->title;
            $model_id = $request->customer_id;
            $media->upload($file, $title, $model_type, $model_id);
            return $this->getSuccessResponse('success');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function listXray(Request $request) {
        try {
            //           $validation = Validator::make($request->all(),
            //           [
            //               'id' => 'required|integer|exists:customers,id',
            //           ]);
            //           if ($validation->fails()) {
            //               return $this->getValidationErrorResponse('error', $validation->errors());
            //           }
            $media = new VeMediaController();
            $data = $media->getMediaByModelId('xrayimage', $request->id);
            foreach ($data as $item) {
                $item->file = url('uploads/media/xrayimage/' . $item->file);
            }
            return $this->getSuccessResponse($data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function deleteXray(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => [
                    'required',
                    'integer',
                    //                    Rule::exists('media', 'id')->where(function ($query) use ($request) {
                    //                        $query->where('model_id', $request->customer_id);
                    //                    }),
                ],
                // 'customer_id' => 'required|integer|exists:customers,id',
            ]);
            if ($validator->fails()) {
                return $this->getValidationErrorResponse('error', $validator->errors());
            }
            $media = new VeMediaController();
            $media->deleteMedia($request->id);
            return $this->getSuccessResponse('success');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function show_dental(Request $request) {
        try {
            $request->validate([
                'id' => 'required|integer|exists:customers,id',
            ]);

            $data = DB::table('additional_information_metadata')
                ->join('additional_information', 'additional_information.id', '=', 'additional_information_metadata.additional_info_id')
                ->where('additional_information.customer_id', $request->id)
                ->where('additional_information.type', 'dental_chart')
                ->where('additional_information_metadata.customer_id', $request->id)
                ->select(
                    'additional_information_metadata.id',
                    'additional_information_metadata.key',
                    'additional_information_metadata.created_at',
                    'additional_information.type',
                    DB::raw('CAST(additional_information_metadata.value AS json) as value')
                )
                ->get();
            // return notes from additional_information_metadata.value
            foreach ($data as $item) {
                $item->value = json_decode($item->value);
            }
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function update_dental(Request $request) {
        try {
            $request->validate([
                'id' => 'required|integer|exists:customers,id',
                //  'value' => 'json',
            ]);
            $data = DB::table('additional_information_metadata')
                ->insert([
                    'additional_info_id' => AdditionalInformation::where('customer_id', $request->id)
                        ->where('type', 'dental_chart')->first()->id,
                    'customer_id' => $request->id,
                    'key' => $request->key,
                    'value' => json_encode($request->value),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    //    public function favorite(Request $request)
    //    {
    //        try {
    //            $request->validate([
    //                'customer_id' => ['required', 'integer', 'exists:customers,id'],
    //                'provider_id' => ['required', 'integer', 'exists:providers,id'],
    //            ]);
    //
    //            $existingFavorite = DB::table('customer_favorite')
    //                ->where('customer_id', $request->customer_id)
    //                ->where('provider_id', $request->provider_id)
    //                ->exists();
    //
    //            if ($existingFavorite) {
    //                DB::table('customer_favorite')
    //                    ->where('customer_id', $request->customer_id)
    //                    ->where('provider_id', $request->provider_id)
    //                    ->delete();
    //
    //                return $this->getSuccessResponse('success', ['response' => 'removed from favorite']);
    //            } else {
    //                DB::table('customer_favorite')
    //                    ->insert(['customer_id' => $request->customer_id, 'provider_id' => $request->provider_id]);
    //
    //                return $this->getSuccessResponse('success', ['response' => 'added to favorite']);
    //            }
    //        } catch (\Throwable $th) {
    //            Logger::error($th->getMessage());
    //            return $this->getErrorResponse('error', $th->getMessage());
    //        }
    //    }
    //
    //    // show one customer favorite list
    //    public function favoriteList(Request $request)
    //    {
    //        try {
    //            // validate id
    //            $validator = Validator::make($request->all(), [
    //                'customer_id' => ['required', 'integer', 'exists:customers,id'],
    //            ]);
    //            if ($validator->fails()) {
    //                return $this->getValidationErrorResponse('error', $validator->errors());
    //            }
    //
    //            $data = DB::table('customer_favorite')
    //                ->select('customer_favorite.id', 'customer_favorite.provider_id')
    //                ->join('providers', 'customer_favorite.provider_id', '=', 'providers.id')
    //                ->where('customer_favorite.customer_id', $request->customer_id)
    //                ->paginate(10);
    //
    //            return $this->getSuccessResponse('success', $data);
    //        } catch (\Throwable $th) {
    //            Logger::error($th->getMessage());
    //            return $this->getErrorResponse('error', $th->getMessage());
    //        }
    //    }

    //    // add and remove from blacklist
    //    public function block(Request $request)
    //    {
    //        try {
    //            // validate id
    //            $validator = Validator::make($request->all(), [
    //                'customer_id' => ['required', 'integer', 'exists:customers,id'],
    //                'provider_id' => ['required', 'integer', 'exists:providers,id'],
    //            ]);
    //            if ($validator->fails()) {
    //                return $this->getValidationErrorResponse('error', $validator->errors());
    //            }
    //            // check if already in blacklist exist remove it else add it
    //            $existingBlacklist = DB::table('block')
    //                ->where('customer_id', $request->customer_id)
    //                ->where('provider_id', $request->provider_id)
    //                ->exists();
    //
    //            if ($existingBlacklist) {
    //                DB::table('block')
    //                    ->where('customer_id', $request->customer_id)
    //                    ->where('provider_id', $request->provider_id)
    //                    ->delete();
    //            } else {
    //                DB::table('block')
    //                    ->insert(['customer_id' => $request->customer_id,
    //                        'provider_id' => $request->provider_id, 'action_by' => '1']);
    //            }
    //            return $this->getSuccessResponse('success', ['response' => 'success']);
    //        } catch (\Throwable $th) {
    //            Logger::error($th->getMessage());
    //            return $this->getErrorResponse('error', $th->getMessage());
    //        }
    //    }
    //
    //    // get customr blacklist
    //    public function blacklistList(Request $request)
    //    {
    //        try {
    //            // validate id
    //            $validator = Validator::make($request->all(), [
    //                'customer_id' => ['required', 'integer', 'exists:customers,id'],
    //            ]);
    //            if ($validator->fails()) {
    //                return $this->getValidationErrorResponse('error', $validator->errors());
    //            }
    //            $data = DB::table('block')
    //                ->select('block.id', 'block.customer_id', 'block.provider_id')
    //                ->join('customers', 'block.customer_id', '=', 'customers.id')
    //                ->where('block.customer_id', $request->customer_id)
    //                ->paginate(10);
    //            return $this->getSuccessResponse('success', $data);
    //        } catch (\Throwable $th) {
    //            Logger::error($th->getMessage());
    //            return $this->getErrorResponse('error', $th->getMessage());
    //        }
    //    }

}
