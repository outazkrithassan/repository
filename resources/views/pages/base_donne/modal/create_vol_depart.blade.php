@php
    $route = route('depart.store');
    $saison = '';
    $numero_depart = '';
    $depart = '';
    $Heure_depart = '';
    $distance = '';
    $date_vol = '';
    $compagnie = '';
    $avion = '';
    $action = 'Ajouter';

    if (!empty($vol_depart)) {
        $route = route('depart.update', ['id' => $vol_depart->id]);
        $saison = $vol_depart->saison_id;
        $numero_depart = $vol_depart->numero;
        $depart = $vol_depart->destination;
        $Heure_depart = $vol_depart->heure_depart;
        $distance = $vol_depart->distance;
        $date_vol = $vol_depart->date_vol;
        $compagnie = $vol_depart->companie_id;
        $avion = $vol_depart->avion_id;
        $action = 'Modifier';
    }

@endphp

<div class="modal-content">
    <div class="modal-header bg-light p-3">
        <h5 class="modal-title" id="exampleModalLabel">{{ $action }} Vol_Deaprt </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
    </div>
    <form id="vol_depart_form" class="tablelist-form" enctype="multipart/form-data" autocomplete="off">
        <div class="modal-body">
            <div class="row">
                @csrf

                <div class="row g-3">
                        {{-- <label class="form-label" for="">Saison</label> --}}
                    <x-select
                            col="col-xxl-2 col-sm-3"
                            label="Saison"
                            id="saison"
                            class="form-control form_element"
                            name="saison"
                            :required="true"
                            placeholder="Select Season"
                            :selected="$saison"
                            :options="$saisons->pluck('annee', 'id')->toArray()"
                            :required="true"
                    />
                    <div class="col-xxl-2 col-sm-3">
                        <label class="form-label" for="">N° depart</label>
                        <input class="form-control" type="text" name="numero_depart" id="numero_depart" value={{ $numero_depart }}>

                    </div>
                    <div class="col-xxl-2 col-sm-3">
                        <label class="form-label" for="">Depart</label>
                        <input class="form-control" type="text" name="depart" id="depart" value={{ $depart }}>
                    </div>
                    <div class="col-xxl-2 col-sm-3">
                        <label class="form-label" for="">Heure depart</label>
                        <input class="form-control" type="text" name="Heure_depart" id="Heure_depart" value={{ $Heure_depart }}>
                    </div>
                    <div class="col-xxl-2 col-sm-3">
                        <label class="form-label" for="">Distance</label>
                        <input class="form-control" type="text" name="distance" id="distance" value={{ $distance }}>
                    </div>
                    <div class="col-xxl-2 col-sm-3">
                        <label class="form-label" for="date_vol">Date Vol</label>
                        <input type="date" class="form-control" id="date_vol" name="date_vol"onchange="formatDate(this)" value={{ $date_vol }}>
                    </div>
                    <x-select
                        col="col-xxl-2 col-sm-3"
                        label="Companie"
                        id="compagnie"
                        name="compagnie"
                        class="form-control filter_data"
                        placeholder="Select Companie"
                        :selected="$compagnie"
                        :options="$companies->pluck('nom', 'id')->toArray()"
                        :required="true"
                    />

                    <x-select
                        col="col-xxl-2 col-sm-3"
                        label="Avion"
                        id="avion"
                        name="avion"
                        class="form-control"
                        placeholder="Select Avion"
                        :selected="$avion"
                        :options="$avions->pluck('equipement', 'id')->toArray()"
                        :required="true"
                    />


                </div>


            </div>

        </div>
        <div class="modal-footer">
            <div class="hstack gap-2 justify-content-end">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                <button route="{{ $route }}" form="vol_depart_form" table="vol_dapart" type="button"
                    class="btn btn-success" id="send_form">
                    {{ $action }}
                </button>
            </div>
        </div>
    </form>
</div>
