<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Site;

class DashboardController extends Controller
{
    public function index()
    {
        $contracts = Contract::all();
        $sites = Site::all();
        return view('dashboard', ['sites' => $sites, 'contracts' => $contracts]);
    }

    public function indexContracts()
    {
        $contracts = Contract::all();
        $sites = Site::all();
        return view('dashboard-contracts', ['contracts' => $contracts]);
    }

    public function indexSites()
    {
        $contracts = Contract::all();
        $sites = Site::all();
        return view('dashboard-sites', ['sites' => $sites]);
    }

    public function getHubSpotSchemas()
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.hubspot.com',
            'headers' => [
                'Authorization' => "Bearer {$_ENV['HUBSPOT_ACCESS_KEY']}",
                'Content-type' => 'application/json'
            ]
        ]);

        try {
            $schemasRequest = $client->request('GET', "/crm/v3/schemas", []);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
        $schemasResult = json_decode($schemasRequest->getBody()->getContents());
        dump($schemasResult);
        die();
    }

    public function syncHubSpotContracts()
    {
        $client = new \GuzzleHttp\Client([
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

            try {
                $contractsRequest = $client->request('POST', "/crm/v3/objects/contracts/search", $config);
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                return $e->getResponse()->getBody()->getContents();
            }

            $contractsResponse = json_decode($contractsRequest->getBody()->getContents());
            $allContracts = array_merge($allContracts, $contractsResponse->results);

            $body['after'] = $contractsResponse->paging->next->after ?? null;

            sleep(1); // HubSpot API rate limit 4 req/s

        } while (count($contractsResponse->results) >= 100);



        if (empty($allContracts)) return redirect(route('dashboard'))->with('notification', 'Yikes!');

        foreach ($allContracts as $contract) {
            $contractData = [
                'hs_object_id' => $contract->properties->hs_object_id,
                'hs_contract_name' => $contract->properties->contract_name,
                'hs_contract_url' => $contract->properties->url,
                'hs_pipeline_stage' => $contract->properties->hs_pipeline_stage
            ];

            $newContract = Contract::create($contractData);
        }

        return redirect(route('dashboard'))->with('notification', 'Success?');
    }

    public function syncMWPluginStatus()
    {
    }

    public function syncUptimeRobot()
    {
    }
}
