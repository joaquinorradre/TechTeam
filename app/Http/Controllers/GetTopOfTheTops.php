<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utils\Methods;
use Illuminate\Support\Facades\DB;

class GetTopOfTheTops extends Controller
{
    protected $ddb;

    public function __construct(DB $ddb)
    {
        $this->db = $ddb;
    }

    public function fetchData(Request $request, Methods $methods)
    {
        $clientId = 'szp2ugo2j6edjt8ytdak5n2n3hjkq3';
        $clientSecret = '07gk0kbwwzpuw2uqdzy1bjnsz9k32k';
        $accessToken = $methods->requestAccessToken($clientId, $clientSecret);

        if (!$accessToken) {
            return response()->json(["error" => "Failed to obtain access token"], 500);
        }

        $gamesTwitchUrl = 'https://api.twitch.tv/helix/games/top?first=3';
        $gamesResponse = $methods->fetchTwitchData($gamesTwitchUrl, $clientId, $accessToken);

        if (!$gamesResponse || !isset($gamesResponse['data'])) {
            return response()->json(["error" => "Failed to fetch games data"], 500);
        }

        // Obtener IDs de los 3 juegos más vistos
        $topGameIds = collect($gamesResponse['data'])->pluck('id')->toArray();

        $results = $this->db::table('Video as v')
            ->select(
                'v.game_id',
                'g.game_name',
                'v.user_name',
                $this->db::raw('total_videos.total_videos AS total_videos'),
                $this->db::raw('total_views.total_views AS total_views'),
                'v.title AS most_viewed_title',
                'v.view_count AS most_viewed_views',
                'v.duration AS most_viewed_duration',
                'v.created_at AS most_viewed_created_at'
            )
            ->join('Game as g', 'v.game_id', '=', 'g.game_id')
            ->join($this->db::raw('(SELECT game_id, MAX(view_count) AS max_view_count FROM Video 
            GROUP BY game_id) AS max_views_per_game'), function ($join) {
                $join->on('v.game_id', '=', 'max_views_per_game.game_id')
                    ->on('v.view_count', '=', 'max_views_per_game.max_view_count');
            })
            ->join($this->db::raw('(SELECT game_id, user_name, COUNT(*) AS total_videos FROM Video 
            GROUP BY game_id, user_name) AS total_videos'), function ($join) {
                $join->on('v.game_id', '=', 'total_videos.game_id')
                    ->on('v.user_name', '=', 'total_videos.user_name');
            })
            ->join($this->db::raw('(SELECT game_id, user_name, SUM(view_count) AS total_views FROM Video 
            GROUP BY game_id, user_name) AS total_views'), function ($join) {
                $join->on('v.game_id', '=', 'total_views.game_id')
                    ->on('v.user_name', '=', 'total_views.user_name');
            })
            ->whereIn('v.game_id', $topGameIds)
            ->get();

        $data = [];
        foreach ($results as $row) {
            $rowData = [
                "game_id" => $row->game_id,
                "game_name" => $row->game_name,
                "user_name" => $row->user_name,
                "total_videos" => strval($row->total_videos),
                "total_views" => $row->total_views,
                "most_viewed_title" => $row->most_viewed_title,
                "most_viewed_views" => strval($row->most_viewed_views),
                "most_viewed_duration" => $row->most_viewed_duration,
                "most_viewed_created_at" => $row->most_viewed_created_at
            ];

            $data[] = $rowData;
        }

        return response()->json($data, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
