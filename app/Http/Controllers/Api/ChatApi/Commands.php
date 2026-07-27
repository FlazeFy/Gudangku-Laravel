<?php

namespace App\Http\Controllers\Api\ChatApi;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

// Service
use App\Service\AIService;
// Helpers
use App\Helpers\Validation;
use App\Helpers\Generator;

class Commands extends Controller
{
    protected AIService $ai;

    public function __construct(AIService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * @OA\POST(
     *     path="/api/v1/chat/ai",
     *     summary="Post Chat (AI)",
     *     description="This AI request is used to do analyze and find data using command (prompt) to select context query. This request interacts with the MySQL database.",
     *     tags={"Chat"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"question"},
     *             @OA\Property(property="question", type="string", example="can you find my vehicle with the most trip?"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="chat answered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="lorem ipsum")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="{validation_msg}",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="{field validation message}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function postChat(Request $request) {
        try {
            // Validate request body
            $validator = Validation::getValidateChat($request);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->messages(),
                ], Response::HTTP_BAD_REQUEST);
            }
    
            $user_id = $request->user()->id;
            $question = trim($request->question);
            $cacheKey = "ai_chat_{$user_id}_".md5(strtolower($question));
    
            // Caching pipeline
            $result = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($question, $user_id) {
                // Select SQL 
                $sql = $this->ai->selectSQLFromExamples($question);
    
                // Basic guard & extract param
                if (empty($sql) || !str_starts_with(strtolower($sql), 'select')) throw new \Exception("Invalid SQL from AI");
                $params = $this->ai->extractParams($question);
    
                // Inject created_by manually (based on your pattern) & build bindings 
                $lowerSql = strtolower($sql);

                // Detect first clause position
                $pos = null;
                foreach ([' order by ', ' group by ', ' limit '] as $k) {
                    if (($p = strpos($lowerSql, $k)) !== false) {
                        $pos = $p;
                        break;
                    }
                }

                // Split or fallback
                [$before, $after] = $pos !== null ? [substr($sql, 0, $pos), substr($sql, $pos)] : [$sql, ''];

                // Inject 
                $sql = $before.(str_contains(strtolower($before), 'where') ? ' AND created_by = ?' : ' WHERE created_by = ?').$after;
                $bindings = $this->ai->buildBindings($params, $user_id);
    
                // Execute query
                $res = DB::select($sql, $bindings);

                // Generate narration
                $text = $this->ai->generateNarration($question, $res);
    
                return [
                    'message' => $text
                ];
            });
    
            return response()->json([
                'status' => 'success',
                'message' => $result['message']
            ], Response::HTTP_OK);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}