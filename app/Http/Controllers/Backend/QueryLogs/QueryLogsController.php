<?php

namespace App\Http\Controllers\Backend\QueryLogs;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use OpenAI;

class QueryLogsController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        try {
            return view('backend.query-logs.index');
        } catch (Exception $ex) {
            Log::info($ex);
        }
    }

    /**
     * Open chat view.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function openChat()
    {
        try {
            Session::put('isChat', true);

            return view('backend.chat.index');
        } catch (Exception $ex) {
            Log::info($ex);
        }
    }

    public function callOpenAIChatAPI(Request $request)
    {
        try {
            if (Session::has('dbSchema')) {
                $schema = Session::get('dbSchema');
            } else {
                $schema = $request->schema;
            }
            $userText = $request->userText;
            $language = $request->language;
            $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
            $apiKey = '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';
            $client = new Client();
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiKey,
                ],
                'json' => [
                    'model' => 'mixtral-8x7b-32768',
                    'messages' => [
                        ['role' => 'system', 'content' => "You are a keyword extractor and mysql database engineer, you have access to the following user's question: <br>" . $userText],
                        ['role' => 'user', 'content' => "Identify the key words in the following user question and then generate a MySQL query (Without Explanation) that retrieves the data from the 'body' column of the 'blogs' table where the keywords match the data in the 'body' column, and only return the data from the 'body' column. Use ' OR ' instead of 'AND' in query and give only query not any other explanation: <br>" . $userText],
                    ],
                    'max_tokens' => 600,
                    "temperature" => 0.5,
                    "top_p" => 1,
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);

            $result = $responseData['choices'][0]['message']['content'];

            if (Session::has('dbSchema')) {
                $config = Session::get('dynamic_data');
                Config::set('database.connections.dynamic', $config);
                Config::set('database.default', 'dynamic');
                $resultData = DB::connection('dynamic')->select($result);

                if ($resultData) {
                    $paragraph = '';
                    foreach ($resultData as $data) {
                        $paragraph .= $data->body;
                    }

                    $dbResponse = $client->post($apiUrl, [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer ' . $apiKey,
                        ],
                        'json' => [
                            'model' => 'mixtral-8x7b-32768',
                            'messages' => [
                                ['role' => 'system', 'content' => "You are a helpful assistant, the paragraph you are working with is:" . $paragraph],
                                ['role' => 'user', 'content' => "Formulate a answer for the following user's question: " . $userText],
                            ],
                            'max_tokens' => 600,
                            "temperature" => 0.7,
                            // "top_p" => 1,
                        ],
                    ]);
                    $dbResponseData = json_decode($dbResponse->getBody(), true);

                    $dbResult = $dbResponseData['choices'][0]['message']['content'];

                    return response()->json($dbResult);
                } else {
                    return response()->json("I'm sorry, but I'm currently experiencing some technical difficulties. I'll be back online shortly.");
                }
            }

            return response()->json($result);
        } catch (\Exception $exception) {
            Log::error($exception);

            return response()->json("I'm sorry, but I'm currently experiencing some technical difficulties. I'll be back online shortly.");
        }
    }

    public function validateSchema(Request $request)
    {
        $request->validate([
            'schema' => [
                'required',
                Rule::custom(function ($value, $attribute) {
                    // Convert the textarea value to an array of SQL statements
                    $statements = array_filter(array_map('trim', explode(';', $value)));

                    // Validate each SQL statement in the array
                    foreach ($statements as $statement) {
                        // Validate the SQL statement using the `Schema` facade
                        if (!Schema::hasValidGrammar($statement)) {
                            return false;
                        }
                    }

                    return true;
                })
            ]
        ]);
    }
}
