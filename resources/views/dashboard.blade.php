<x-head/>
<x-header/>

@if(session()->has('notification'))
    <x-notification/>
@endif

<section>
    <div class="container">
        <div class="row">
            <div class="col-12 col-xl-2">
                <h3>Actions</h3>
                <a href="{{route('dashboard.sync-hubspot-contracts')}}">Sync HubSpot</a>
                
                <h5 class="mt-5"><i>Debugging</i></h5>
                <a href="{{route('dashboard.get-hubspot-schemas')}}" target="_blank">Get HubSpot schemas</a>
            </div>
            <div class="col-12 col-xl-7">
                <h3>Contracts</h3>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Url</th>
                        <th>Stage</th>
                    </tr>
                    @foreach ($contracts as $contract)
                        <tr>
                            <td>{{$contract->hs_contract_name}}</td>
                            <td>{{$contract->hs_contract_url}}</td>
                            <td>{{$contract->hs_pipeline_stage}}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <div class="col-12 col-xl-2">
                <h3>Sites</h3>
                <table>
                    <tr>
                        <th>Url</th>
                        <th>Name</th>
                        <th>UptimeRobot</th>
                        <th>ManageWP</th>
                    </tr>
                    @foreach ($sites as $site)
                        <tr>
                            <td>{{$site->url}}</td>
                            <td>{{$site->name}}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<x-footer/>