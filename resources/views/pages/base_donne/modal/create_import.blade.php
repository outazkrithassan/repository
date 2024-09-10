@php
    $saison = [];
    $route = route('import.store');
    $action = 'Ajouter';

    if (!empty($saison)) {
        $route = '';
        $action = 'Modifier';
    }

@endphp

<div class="modal-content">
    <div class="modal-header bg-light p-3">
        <h5 class="modal-title" id="exampleModalLabel"> {{ $action }} saison </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
    </div>
    <form id="saison_form" class="tablelist-form" enctype="multipart/form-data" autocomplete="off">
        <div class="modal-body">
            <div class="row">
                @csrf

                <x-input :attrs="[
                    'col' => 'col-md-6',
                    'label' => 'annee',
                    'id' => 'annee',
                    'class' => 'form-control form_element',
                    'name' => 'annee',
                    'type' => 'text',
                    'required' => 'required',
                    'value' => '',
                    'placeholder' => '...',
                ]" />

                <x-input :attrs="[
                    'col' => 'col-md-6',
                    'label' => 'data',
                    'id' => 'data',
                    'class' => 'form-control form_element',
                    'name' => 'data',
                    'type' => 'file',
                    'required' => 'required',
                    'value' => '',
                    'placeholder' => '...',
                ]" />

            </div>

        </div>
        <div class="modal-footer">
            <div class="hstack gap-2 justify-content-end">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                <button route="{{ $route }}" form="saison_form" table="saison" type="button"
                    class="btn btn-success" id="send_form">
                    {{ $action }}
                </button>
            </div>
        </div>
    </form>
</div>
