@php
    $route = route('arrive.store');
    $saison = '';
    $numero_arrivee = '';
    $arrive = '';
    $heure_arrive = '';
    $distance = '';
    $date_vol = '';
    $compagnie = '';
    $avion = '';
    $action = 'Ajouter';

    if (!empty($vol_arrive)) {
        $route = route('arrive.update', ['id' => $vol_arrive->id]);
        $saison = $vol_arrive->saison_id;
        $numero_arrivee = $vol_arrive->numero;
        $arrive = $vol_arrive->depart;
        $heure_arrive = $vol_arrive->heure_arrive;
        $distance = $vol_arrive->distance;
        $date_vol = $vol_arrive->date_vol;
        $compagnie = $vol_arrive->companie_id;
        $avion = $vol_arrive->avion_id;
        $action = 'Modifier';
    }

@endphp

<div class="modal-content">
    <div class="modal-header bg-light p-3">
        <h5 class="modal-title" id="exampleModalLabel">{{ $action }} Vol_Arrivee </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
    </div>
    <form id="vol_arrivee_form" class="tablelist-form" enctype="multipart/form-data" autocomplete="off">
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
                        <label class="form-label" for="">N° arrivee</label>
                        <input class="form-control" type="text" name="numero_arrivee" id="numero_arrivee" value={{ $numero_arrivee }}>

                    </div>
                    <div class="col-xxl-2 col-sm-3">
                        <label class="form-label" for="">Arrivee</label>
                        <input class="form-control" type="text" name="arrive" id="arrive" value={{ $arrive }}>
                    </div>
                    <div class="col-xxl-2 col-sm-3">
                        <label class="form-label" for="">Heure arrivee</label>
                        <input class="form-control" type="text" name="heure_arrive" id="heure_arrive" value={{ $heure_arrive }}>
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
                <button route="{{ $route }}" form="vol_arrivee_form" table="vol_arrivee" type="button"
                    class="btn btn-success" id="send_form">
                    {{ $action }}
                </button>
            </div>
        </div>
    </form>
</div>
