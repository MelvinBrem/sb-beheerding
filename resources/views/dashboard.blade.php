<x-head/>
<x-header/>

@if(session()->has('notification'))
    <x-notification/>
@endif

<section>
    <div class="container">
        <div class="row">
            <div class="col-3">
                <h5>Actions</h5>
                <a href="{{route('dashboard.sync-hubspot-contracts')}}">Sync HubSpot</a><br>
                <a href="{{route('dashboard.sync-uptimerobot')}}">Sync UptimeRobot</a><br>
                <a href="{{route('dashboard.sync-managewp')}}">Sync ManageWP</a>
                {{-- <a href="{{route('dashboard.sync-ghostinspector')}}">Sync UptimeRobot</a> --}}
            </div>
            <div class="col-9">
                <h5>Debugging</h5>
                <a href="{{route('dashboard.get-hubspot-schemas')}}" target="_blank">Get HubSpot schemas</a>
            </div>
        </div>
        <div class="row">
            <div class="mt-5 col-12">
                <h3>Contracts</h3>
                <table class="sortable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Url</th>
                            <th width="250px">Stage</th>
                            <th>UTR</th>
                            <th>MWP</th>
                            <th>GI</th>
                        </tr>
                    </thead>
                    <tbody
                    @foreach ($contracts as $contract)
                        <tr>
                            <td>{{$contract->hs_contract_name}}</td>
                            <td>{{$contract->hs_contract_url}}</td>
                            <td>{{$contract->hs_pipeline_stage}}</td>
                            <td>{{$contract->utr_status}}</td>
                            <td>{{$contract->mwp_status}}</td>
                            <td></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<x-footer/>