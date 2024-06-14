<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\GetTopOfTheTopsService;

class GetTopOfTheTopsController extends Controller
{
    private GetTopOfTheTopsService $topOfTheTopsService;

    public function __construct(
        GetTopOfTheTopsService $topOfTheTopsService,
    ) {
        $this->topOfTheTopsService = $topOfTheTopsService;
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): JsonResponse
    {
        $since = $request->input('since', 600);
        $topOfTheTops = $this->topOfTheTopsService->execute($since);

        return new JsonResponse($topOfTheTops, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
