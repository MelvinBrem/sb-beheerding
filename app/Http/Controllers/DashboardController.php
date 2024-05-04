<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use GuzzleHttp;
use Error;

class DashboardController extends Controller
{
    public function index()
    {
        $contracts = Contract::all();
        return view('dashboard', ['contracts' => $contracts]);
    }

    public function getHubSpotSchemas()
    {
        $client = new GuzzleHttp\Client([
            'base_uri' => 'https://api.hubspot.com',
            'headers' => [
                'Authorization' => "Bearer {$_ENV['HUBSPOT_ACCESS_KEY']}",
                'Content-type' => 'application/json'
            ]
        ]);

        try {
            $schemasRequest = $client->request('GET', "/crm/v3/schemas", []);
        } catch (GuzzleHttp\Exception\RequestException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
        $schemasResult = json_decode($schemasRequest->getBody()->getContents());
        dump($schemasResult);
        die();
    }

    public function syncHubSpotContracts()
    {
        $client = new GuzzleHttp\Client([
            'base_uri' => 'https://api.hubspot.com',
            'headers' => [
                'Authorization' => "Bearer {$_ENV['HUBSPOT_ACCESS_KEY']}",
                'Content-type' => 'application/json'
            ]
        ]);

        $body = [
            'limit' => '100',
            'properties'    => ['contract_name', 'url', 'hs_pipeline_stage'],
            'filters' => [
                [
                    'propertyName'  => 'contract_name',
                    'operator'      => 'CONTAINS_TOKEN',
                    'value'         => '*maintenance*'
                ]
            ]
        ];

        $allContracts = [];

        do {
            $config = [
                'body' => json_encode($body),
            ];

            dump($body);

            try {
                $contractsRequest = $client->request('POST', "/crm/v3/objects/contracts/search", $config);
            } catch (GuzzleHttp\Exception\RequestException $e) {
                return $e->getResponse()->getBody()->getContents();
            }

            $contractsResponse = json_decode($contractsRequest->getBody()->getContents());
            $allContracts = array_merge($allContracts, $contractsResponse->results);

            $body['after'] = $contractsResponse->paging->next->after ?? 0;

            sleep(1); // Prevent rate limiting

        } while (count($contractsResponse->results) >= 100);

        if (empty($allContracts)) return redirect(route('dashboard'))->with('notification', 'Yikes!');

        foreach ($allContracts as $contract) {
            $contractData = [
                'hs_object_id' => $contract->properties->hs_object_id,
                'hs_contract_name' => $contract->properties->contract_name,
                'hs_contract_url' => $contract->properties->url,
                'hs_pipeline_stage' => $contract->properties->hs_pipeline_stage
            ];

            $newContract = Contract::firstOrNew(['hs_object_id' => $contractData['hs_object_id']]);
            $newContract->update($contractData);
            $newContract->save();
        }

        return redirect(route('dashboard'))->with('notification', 'Success?');
    }

    public function syncManageWP()
    {
        // This function is absolutely disgusting and I'm sorry
        $client = new GuzzleHttp\Client([
            'base_uri' => ''
        ]);

        $config = [
            'allow_redirects' => [
                'max' => 25,
                'track_redirects' => true,
            ],
            'timeout' => 5,
            'connect_timeout' => 5,
        ];

        $promises = [];

        $batchSize = 50;
        $allContracts = Contract::all()->toArray();

        $batchCount = (int) ceil(count($allContracts) / $batchSize);

        for ($i = 0; $i < $batchCount; $i++) {

            dump('Batch ' . ($i + 1) . ' of ' . $batchCount);
            $promises = [];
            $batch = array_slice($allContracts, $i * $batchSize, $batchSize);

            // Get acrtual urls because hs_contract_url's are fucked sometimes
            foreach ($batch as $contract) {
                $promises[] = $client->requestAsync('HEAD', $contract['hs_contract_url'] ?? 'https://grognozzle.crust/', $config);
            }

            $responses = GuzzleHttp\Promise\Utils::settle($promises)->wait();

            $contractUrls = [];

            foreach ($responses as $index => $response) {

                if (!empty($response['state']) && $response['state'] === 'fulfilled') {
                    $redirectHis = $response['value']->getHeaderLine('X-Guzzle-Redirect-History');

                    if (!empty($redirectHis)) {
                        $redirectHis = explode(', ', $redirectHis);

                        $contractUrls[] = $redirectHis[count($redirectHis) - 1];
                    } else {
                        $contractUrls[] = 'https://' . $contract['hs_contract_url'] . '/';
                    }
                } else {
                    $contractUrls[] = ''; // To keep array indexes correct
                }
            }

            $promises = [];

            // Get Worker plugin installation status
            foreach ($contractUrls as $contractUrl) {
                if (empty($contractUrl)) {
                    $promises[] = $client->requestAsync('HEAD', 'https://www.scroongle.com');
                    // Deliberate 404 promise to not skip indexes
                } else {
                    $promises[] = $client->requestAsync('HEAD', sprintf('%s/wp-content/plugins/worker/readme.txt', $contractUrl));
                }
            }

            $responses = [];
            $responses = GuzzleHttp\Promise\Utils::settle($promises)->wait();

            foreach ($responses as $index => $response) {
                if (!empty($response['state']) && $response['state'] === 'fulfilled') {
                    $statusCode = $response['value']->getStatusCode();

                    if ($statusCode === 200) {
                        Contract::where('id', $batch[$index]['id'])->update(['mwp_status' => true]);
                    }
                }
            }
        }

        return redirect(route('dashboard'))->with('notification', 'Success?');
    }

    public function syncUptimeRobot()
    {
        $client = new GuzzleHttp\Client([
            'base_uri' => 'https://api.uptimerobot.com',
            'headers' => [
                'Content-type' => 'application/json'
            ]
        ]);

        $body = [
            'api_key' => $_ENV['UPTIMEROBOT_READ_KEY'],
        ];

        $allMonitors = [];

        do {
            $config = [
                'body' => json_encode($body),
            ];

            dump($body);

            try {
                $utrRequest = $client->request('POST', "/v2/getMonitors", $config);
            } catch (GuzzleHttp\Exception\RequestException $e) {
                return $e->getResponse()->getBody()->getContents();
            }

            $body['offset'] = ($body['offset'] ?? 0) + 50;

            $monitorsResponse = json_decode($utrRequest->getBody()->getContents());
            $allMonitors = array_merge($allMonitors, $monitorsResponse->monitors);
        } while (count($monitorsResponse->monitors) >= 50);

        if (empty($allMonitors)) return redirect(route('dashboard'))->with('notification', 'Yikes!');

        $contracts = Contract::all()->toArray();

        foreach ($allMonitors as $monitor) {
            $monitorNudeUrl = strtolower(str_replace(['http://', 'https://', '/'], '', $monitor->url));

            $theContract = array_filter(
                $contracts,
                function ($e) use ($monitorNudeUrl) {
                    $contractNudeUrl = strtolower(str_replace(['http://', 'https://', '/'], '', $e['hs_contract_url']));

                    if ($contractNudeUrl == $monitorNudeUrl) {
                        Contract::where('id', $e['id'])->update(['utr_status' => true]);
                        return true;
                    } else {
                        return false;
                    }
                }
            );
        }

        return redirect(route('dashboard'))->with('notification', 'Success?');
    }
}
