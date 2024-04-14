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
            <div class="col-12 col-xl-10 mt-5 mt-xl-0">
                <h3>Contracts</h3>
                <table class="sortable">
                    <thead>
                        <tr>
                            <th><span>Name</span></th>
                            <th><span>Url</span></th>
                            <th width="250px"><span>Stage</span></th>
                        </tr>
                    </thead>
                    <tbody
                    @foreach ($contracts as $contract)
                        <tr>
                            <td>{{$contract->hs_contract_name}}</td>
                            <td>{{$contract->hs_contract_url}}</td>
                            <td>{{$contract->hs_pipeline_stage}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<x-footer/>