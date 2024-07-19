@php
    $route = route('frere.store');
    $numero_arrivee = '';
    $numero_depart = '';
    $action = 'Ajouter';

    if (!empty($vol_frere)) {
        $route = route('frere.update', ['id' => $vol_frere->id]);
        $numero_arrivee = $vol_frere->numero_arrivee;
        $numero_depart = $vol_frere->numero_depart;
        $action = 'Modifier';
    }

@endphp

<div class="modal-content">
    <div class="modal-header bg-light p-3">
        <h5 class="modal-title" id="exampleModalLabel"> {{ $action }} vol_frere </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
    </div>
    <form id="vol_frere_form" class="tablelist-form" enctype="multipart/form-data" autocomplete="off">
        <div class="modal-body">
            <div class="row">
                @csrf

                <x-input :attrs="[
                    'col' => 'col-md-6',
                    'label' => 'Numéro vol',
                    'id' => 'numero_arrivee',
                    'class' => 'form-control form_element',
                    'name' => 'numero_arrivee',
                    'type' => 'number',
                    'required' => 'required',
                    'value' => $numero_arrivee,
                    'placeholder' => '...',
                ]" />

                <x-input :attrs="[
                    'col' => 'col-md-6',
                    'label' => 'Numéro vol frére',
                    'id' => 'numero_depart',
                    'class' => 'form-control form_element',
                    'name' => 'numero_depart',
                    'type' => 'number',
                    'required' => 'required',
                    'value' => $numero_depart,
                    'placeholder' => '...',
                ]" />



            </div>

        </div>
        <div class="modal-footer">
            <div class="hstack gap-2 justify-content-end">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                <button route="{{ $route }}" form="vol_frere_form" table="vol_frere" type="button"
                    class="btn btn-success" id="send_form">
                    {{ $action }}
                </button>
            </div>
        </div>
    </form>
</div>
