<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Serializers\TopsOfTheTopsDataSerializer;
use App\Services\GetTopOfTheTopsService;

class GetTopOfTheTopsController extends Controller
{
    private GetTopOfTheTopsService $getTopOfTheTopsService;
    private TopsOfTheTopsDataSerializer $topsOfTheTopsDataSerializer;

    public function __construct(
        GetTopOfTheTopsService $getTopOfTheTopsService,
        TopsOfTheTopsDataSerializer $topsOfTheTopsDataSerializer
    ) {
        $this->getTopOfTheTopsService = $getTopOfTheTopsService;
        $this->topsOfTheTopsDataSerializer = $topsOfTheTopsDataSerializer;
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): JsonResponse
    {
        $since = $request->input('since', 600);
        $topOfTheTops = $this->getTopOfTheTopsService->execute($since);
        //$serializedTopOfTheTops = $this->topsOfTheTopsDataSerializer->serialize($topOfTheTops);

        return new JsonResponse($topOfTheTops, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
