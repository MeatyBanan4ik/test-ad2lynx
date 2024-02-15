<?php

namespace App\Http\Controllers;

use App\Actions\GetByEndpointAction;
use App\Actions\MergeDataAction;
use App\Http\Requests\CreateAdRequest;
use App\Models\Ad;
use App\Services\AdService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected AdService $adService;

    public function __construct(AdService $adService)
    {
        $this->adService = $adService;
    }

    public function index(Request $request) {
        return view('welcome', [
            'ads' => Ad::where(function ($query) use ($request) {
                    if($search = $request->input('ad_id')) {
                        $query->where('ad_id', $search);
                    }
                })
                ->orderBy('impressions', 'ASC')
                ->get()
        ]);
    }

    public function create(CreateAdRequest $request, GetByEndpointAction $getByEndpointAction, MergeDataAction $mergeDataAction) {
        ['endpoint1' =>  $endpoint1, 'endpoint2' => $endpoint2] = $request->validated();

        try {
            $endpoint1Data = $getByEndpointAction->handle($endpoint1);
            $endpoint2Data = $getByEndpointAction->handle($endpoint2);

            $mergedData = $mergeDataAction->handle($endpoint1Data, $endpoint2Data['data']['list']);

            $this->adService->bulkCreate($mergedData);

            return redirect()->route('home.index');
        } catch (\Exception $e) {
            abort(500);
        }
    }
}
