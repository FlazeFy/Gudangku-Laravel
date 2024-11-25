<?php

namespace App\Http\Controllers\Api\DictionaryApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

// Models
use App\Models\dictionaryModel;
use App\Helpers\Validation;

class Commands extends Controller
{
    /**
     * @OA\DELETE(
     *     path="/api/v1/dictionary/destroy/{id}",
     *     summary="Delete dictionary by id",
     *     tags={"Dictionary"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="dictionary ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="dictionary permentally deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="dictionary permentally deleted")
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
     *         response=404,
     *         description="dictionary failed to permentally deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="dictionary not found")
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
    public function hard_delete_dictionary_by_id(Request $request, $id)
    {
        try{
            // Validator
            $request->merge(['id' => $id]);
            $validator = Validation::getValidateDictionary($request,'delete');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                // Service : Delete
                $rows = DictionaryModel::destroy($id);

                // Respond
                if($rows > 0){
                    return response()->json([
                        'status' => 'success',
                        'message' => 'dictionary permentally deleted',
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'dictionary not found',
                    ], Response::HTTP_NOT_FOUND);
                }
            } 
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

   /**
     * @OA\POST(
     *     path="/api/v1/dictionary",
     *     summary="Post dictionary",
     *     description="Create a new dictionary using the given name and category. This request is using MySQL database.",
     *     tags={"Dictionary"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="Dictionary created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/DictionaryResponse"),
     *             @OA\Property(property="message", type="string", example="dictionary created")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="failed"),
     *                     @OA\Property(property="message", type="string", example="dictionary name must be at least 2 characters")
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="failed"),
     *                     @OA\Property(property="message", type="string", example="dictionary type must be one of the following values inventory_category, inventory_unit, inventory_room, reminder_type, reminder_context, report_category")
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="failed"),
     *                     @OA\Property(property="message", type="string", example="dictionary_name is a required field")
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="failed"),
     *                     @OA\Property(property="message", type="string", example="dictionary name has been used. try another")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     )
     * )
     *
     * @OA\Schema(
     *     schema="DictionaryResponse",
     *     type="object",
     *     @OA\Property(property="dictionary_name", type="string", example="Check"),
     *     @OA\Property(property="dictionary_type", type="string", example="inventory_category"),
     *     @OA\Property(property="_id", type="string", example="674342a8b53aabe966070f3d"),
     *     @OA\Property(property="createdAt", type="string", format="date-time", example="2024-11-24T15:13:44.621Z"),
     *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2024-11-24T15:13:44.621Z")
     * )
     */


    public function post_dictionary(Request $request)
    {
        try{
            // Validator
            $validator = Validation::getValidateDictionary($request,'create');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $dictionary_type = $request->dictionary_type;
                $dictionary_name = $request->dictionary_name;

                // Model : Check name dictionary name avaiability
                $isUsedName = DictionaryModel::isUsedName($dictionary_name, $dictionary_type);
                if($isUsedName){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'dictionary name has been used. try another',
                    ], Response::HTTP_CONFLICT);
                } else {
                    // Service : Create
                    $rows = DictionaryModel::create([
                        'dictionary_type' => $dictionary_type,
                        'dictionary_name' => $dictionary_name,
                    ]);

                    // Respond
                    if($rows){
                        return response()->json([
                            'status' => 'success',
                            'message' => 'dictionary created',
                        ], Response::HTTP_CREATED);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'something wrong. please contact admin',
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
