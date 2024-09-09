<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">{{ $title }}</h4>
            {{-- <div class="col-xxl-2 col-sm-2">
                <label class="form-label" for="">Saison</label>
                <select class="form-control" name="choices-single-default" id="saison">
                    <option value="">Select Season</option>
                    @foreach($saisons as $saison)
                        <option value="{{ $saison->id }}">{{ $saison->annee }}</option>
                    @endforeach
                </select>
            </div> --}}
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">{{ $li_1 }}</a></li>
                    @if(isset($title))
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    @endif
                </ol>
            </div>

        </div>
    </div>
</div>
<!-- end page title -->
