<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Serializers\TopsOfTheTopsDataSerializer;
use App\Services\GetTopOfTheTopsService;

class GetTopOfTheTopsController extends Controller
{
    private GetTopOfTheTopsService $topOfTheTopsService;
    private TopsOfTheTopsDataSerializer $topsOfTopsSerializer;

    public function __construct(
        GetTopOfTheTopsService $topOfTheTopsService,
        TopsOfTheTopsDataSerializer $topsOfTopsSerializer
    ) {
        $this->topOfTheTopsService = $topOfTheTopsService;
        $this->topsOfTopsSerializer = $topsOfTopsSerializer;
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): JsonResponse
    {
        $since = $request->input('since', 600);
        $topOfTheTops = $this->topOfTheTopsService->execute($since);
        //$serializedTopOfTheTops = $this->topsOfTheTopsDataSerializer->serialize($topOfTheTops);

        return new JsonResponse($topOfTheTops, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
